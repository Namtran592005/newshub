const API_BASE = 'api.php';
const POLL_INTERVAL = 60000;
const PER_PAGE = 10;

let allData = null;
let breakingScrollTimer = null;
let breakingAutoScroll = true;
let charts = {};
let userCity = '';

const state = {
    articles: [], filtered: [], currentPage: 1, perPage: PER_PAGE,
    filters: { source: 'all', category: 'all', time: 'all', search: '' },
    financeTab: 'all',
};

const SOURCE_COLORS = {
    'VnExpress':'#2d9cdb','Tuổi Trẻ':'#16a34a','Dân trí':'#dc2626','Vietnamnet':'#f97316',
    'Thanh Niên':'#eab308','Znews':'#dc2626','24h':'#06b6d4','VietNamPlus':'#a855f7',
    'BBC News':'#ef4444','TechCrunch':'#f97316','Reddit':'#ff4500','HN Frontpage':'#f97316',
};

const CATEGORY_COLORS = ['#3b82f6','#06b6d4','#22c55e','#f97316','#a855f7','#ef4444','#eab308','#ec4899'];

function cssVar(name, fallback='') {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim()||fallback;
}
function cd(sourceChart=false) {
    const c=cssVar('--chart-text','#5c5f66'), g=cssVar('--chart-grid','#1a1d23');
    return { color:c, border:{color:g}, font:{family:'Inter,sans-serif'}, grid:{color:g,drawBorder:false}, ticks:{color:c,font:{size:sourceChart?8:10}} };
}
function timeAgo(t){const d=Math.floor(Date.now()/1000)-t;if(d<60)return'vài giây';if(d<3600)return Math.floor(d/60)+'ph';if(d<86400)return Math.floor(d/3600)+'gi';return Math.floor(d/86400)+'ng';}
function sourceClass(n){return'src-'+n.toLowerCase().replace(/\s+/g,'-').replace(/đ/g,'d').replace(/[ơở]/g,'o');}
function escHtml(s){if(!s)return'';const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function truncate(s,m){if(!s)return'';return s.length>m?s.slice(0,m)+'…':s;}
function numFormat(n){return(n||0).toLocaleString('vi-VN')}

// ===== FILTERS =====
let filtInit=false;
function initFilters(data){
    if(filtInit)return;
    const src=document.getElementById('filter-source'),cat=document.getElementById('filter-category');
    src.innerHTML='<option value="all">Tất cả nguồn</option>'+[...new Set(data.articles.map(a=>a.source))].sort().map(s=>`<option value="${escHtml(s)}">${escHtml(s)}</option>`).join('');
    cat.innerHTML='<option value="all">Tất cả chuyên mục</option>'+[...new Set(data.articles.map(a=>a.category))].sort().map(c=>`<option value="${escHtml(c)}">${escHtml(c)}</option>`).join('');
    src.onchange=()=>{state.filters.source=src.value;applyFilters();};
    cat.onchange=()=>{state.filters.category=cat.value;applyFilters();};
    document.getElementById('filter-time').onchange=function(){state.filters.time=this.value;applyFilters();};
    let st;document.getElementById('filter-search').oninput=function(){clearTimeout(st);st=setTimeout(()=>{state.filters.search=this.value.trim().toLowerCase();applyFilters();},300);};
    filtInit=true;
}
function applyFilters(){
    const{source,category,time,search}=state.filters;
    let f=state.articles;
    if(source!=='all')f=f.filter(a=>a.source===source);
    if(category!=='all')f=f.filter(a=>a.category===category);
    if(search)f=f.filter(a=>a.title.toLowerCase().includes(search)||a.description.toLowerCase().includes(search));
    if(time!=='all'){const c=Math.floor(Date.now()/1000)-parseInt(time)*3600;f=f.filter(a=>a.pubDate>=c);}
    state.filtered=f;state.currentPage=1;renderNewsPage();renderPagination();
    document.getElementById('filter-count').textContent=f.length+' kết quả';
}
function getPageItems(){const s=(state.currentPage-1)*state.perPage;return state.filtered.slice(s,s+state.perPage);}

// ===== BREAKING NEWS =====
function renderBreaking(articles){
    const t=document.getElementById('breaking-track');
    if(!articles||!articles.length){t.innerHTML='<div style="color:var(--text-muted);font-size:.78rem">Đang tải...</div>';return;}
    t.innerHTML=articles.slice(0,12).map(a=>`
        <a href="${a.link}" target="_blank" rel="noopener" class="breaking-card">
            <div class="bc-meta"><span class="bc-source"><i class="fa-regular fa-circle-dot"></i> ${escHtml(a.source)}</span><span class="bc-time">${timeAgo(a.pubDate)}</span></div>
            <div class="bc-title">${escHtml(a.title)}</div>
            <span class="bc-cat">${a.category}</span>
        </a>`).join('');
    document.getElementById('total-breaking').textContent=articles.length;
}

function startBreakingScroll(){
    const t=document.getElementById('breaking-track');
    clearInterval(breakingScrollTimer);
    if(!breakingAutoScroll)return;
    breakingScrollTimer=setInterval(()=>{
        const card=t.querySelector('.breaking-card');
        if(!card)return;
        const step=card.offsetWidth+8; // card + gap
        if(t.scrollLeft>=t.scrollWidth-t.clientWidth-step)t.scrollTo({left:0,behavior:'smooth'});
        else t.scrollBy({left:step,behavior:'smooth'});
    },3000);
}
function toggleBreakingScroll(){
    breakingAutoScroll=!breakingAutoScroll;
    document.querySelector('#bc-play i').className=breakingAutoScroll?'fa-solid fa-pause':'fa-solid fa-play';
    if(breakingAutoScroll)startBreakingScroll();else clearInterval(breakingScrollTimer);
}
document.addEventListener('DOMContentLoaded',()=>{
    const t=document.getElementById('breaking-track');
    t?.addEventListener('mouseenter',()=>{clearInterval(breakingScrollTimer);});
    t?.addEventListener('mouseleave',()=>{if(breakingAutoScroll)startBreakingScroll();});
    document.getElementById('bc-prev')?.addEventListener('click',()=>{t.scrollBy({left:-300,behavior:'smooth'});});
    document.getElementById('bc-next')?.addEventListener('click',()=>{t.scrollBy({left:300,behavior:'smooth'});});
    document.getElementById('bc-play')?.addEventListener('click',toggleBreakingScroll);
});

// ===== NEWS TICKER =====
function renderTicker(articles){
    const tr=document.getElementById('ticker-track');
    if(!articles||!articles.length){tr.innerHTML='<span class="ticker-placeholder" style="color:var(--text-muted);font-size:.75rem;padding:0 16px">Đang tải...</span>';return;}
    // Double the items for seamless loop
    const items=articles.slice(0,20);
    const render=()=>items.map(a=>`<a href="${a.link}" target="_blank" rel="noopener" class="ticker-item"><span class="ti-source">${escHtml(a.source)}</span><span class="ti-sep">•</span><span class="ti-title">${escHtml(a.title)}</span></a>`).join('<span class="ti-sep" style="margin:0 8px;color:var(--border-color)">◆</span>');
    tr.innerHTML=`<div class="ticker-content">${render()}<span class="ti-sep" style="margin:0 8px;color:var(--border-color)">◆</span>${render()}</div>`;
}

// ===== NEWS FEED =====
function renderNewsPage(){
    const c=document.getElementById('news-feed'),items=getPageItems();
    c.style.opacity='0';c.style.transform='translateY(6px)';
    setTimeout(()=>{
        if(!items.length)c.innerHTML='<div class="news-card" style="text-align:center;padding:30px 16px"><i class="fa-regular fa-newspaper" style="font-size:1.8rem;display:block;margin-bottom:8px;color:var(--text-muted)"></i><div style="color:var(--text-muted);font-size:.85rem">Không có kết quả phù hợp</div></div>';
        else c.innerHTML=items.map((a,i)=>`<a href="${a.link}" target="_blank" rel="noopener" class="news-card" style="--i:${i}"><div class="news-card-meta"><span class="news-source ${sourceClass(a.source)}"><i class="${a.type==='social'?'fa-brands fa-reddit-alien':'fa-regular fa-circle-dot'}"></i>${escHtml(a.source)}</span><span class="news-time"><i class="fa-regular fa-clock"></i>${timeAgo(a.pubDate)} trước</span></div><div class="news-title">${escHtml(a.title)}</div><div class="news-desc">${escHtml(truncate(a.description,160))}</div><span class="news-cat"><i class="fa-solid fa-tag"></i> ${a.category}</span></a>`).join('');
        c.style.opacity='1';c.style.transform='translateY(0)';
    },150);
    const tp=Math.ceil(state.filtered.length/state.perPage)||1;
    document.getElementById('feed-count').textContent=`Trang ${state.currentPage}/${tp} (${state.filtered.length} bài)`;
}
function renderPagination(){
    const c=document.getElementById('pagination'),t=state.filtered.length,tp=Math.ceil(t/state.perPage)||1,p=state.currentPage;
    if(tp<=1){c.innerHTML='';return;}
    let h='';
    h+=`<button class="page-btn" onclick="goPage(1)" ${p===1?'disabled':''}><i class="fa-solid fa-angles-left"></i></button>`;
    h+=`<button class="page-btn" onclick="goPage(${p-1})" ${p===1?'disabled':''}><i class="fa-solid fa-angle-left"></i></button>`;
    let pg=[];
    if(tp<=7){for(let i=1;i<=tp;i++)pg.push(i);}else{pg.push(1);if(p>3)pg.push('…');let s=Math.max(2,p-1),e=Math.min(tp-1,p+1);if(p<=3){s=2;e=4;}if(p>=tp-2){s=tp-3;e=tp-1;}for(let i=s;i<=e;i++)pg.push(i);if(p<tp-2)pg.push('…');pg.push(tp);}
    pg.forEach(x=>{if(x==='…')h+=`<span class="page-info">…</span>`;else h+=`<button class="page-btn${x===p?' active':''}" onclick="goPage(${x})">${x}</button>`;});
    h+=`<button class="page-btn" onclick="goPage(${p+1})" ${p===tp?'disabled':''}><i class="fa-solid fa-angle-right"></i></button>`;
    h+=`<button class="page-btn" onclick="goPage(${tp})" ${p===tp?'disabled':''}><i class="fa-solid fa-angles-right"></i></button>`;
    c.innerHTML=h;
}
function goPage(pg){const tp=Math.ceil(state.filtered.length/state.perPage)||1;if(pg<1||pg>tp||pg===state.currentPage)return;state.currentPage=pg;renderNewsPage();renderPagination();document.querySelector('.news-feed-list')?.scrollIntoView({behavior:'smooth',block:'start'});}

// ===== STATS =====
function renderStats(d){document.getElementById('total-articles').textContent=d.total||0;document.getElementById('total-keywords').textContent=Object.keys(d.top_keywords||{}).length;document.getElementById('total-categories').textContent=Object.keys(d.category_stats||{}).length;if(d.breaking)document.getElementById('total-breaking').textContent=d.breaking.length;}

// ===== SIDEBAR =====
function renderCategories(st){const c=document.getElementById('category-list');if(!st)return;const e=Object.entries(st).sort((a,b)=>b[1]-a[1]),mv=Math.max(...e.map(x=>x[1]),1);c.innerHTML=e.map(([n,v],i)=>`<div class="category-item"><span class="cat-name">${escHtml(n)}</span><div class="cat-bar-wrap"><div class="cat-bar" style="width:${(v/mv)*100}%;background:${CATEGORY_COLORS[i%CATEGORY_COLORS.length]}"></div></div><span class="cat-count">${v}</span></div>`).join('');}
function renderSources(st){const c=document.getElementById('source-list');if(!st)return;const e=Object.entries(st).sort((a,b)=>b[1]-a[1]);c.innerHTML=e.map(([n,v])=>`<div class="source-item"><span class="source-name"><span class="source-dot" style="background:${SOURCE_COLORS[n]||'#5c5f66'}"></span>${escHtml(n)}</span><span class="source-count">${v}</span></div>`).join('');}
function renderKeywords(kw){const c=document.getElementById('keywords-cloud');if(!kw)return;const e=Object.entries(kw).slice(0,25),mc=Math.max(...e.map(x=>x[1]),1);c.innerHTML=e.map(([w,v])=>`<span class="keyword-tag" style="font-size:${0.7+(v/mc)*0.5}rem">${escHtml(w)}<span class="keyword-count">${v}</span></span>`).join('');}
function renderTrending(t){const c=document.getElementById('trending-list');if(!t||!t.length){c.innerHTML='<div style="color:var(--text-muted);font-size:.78rem;text-align:center;padding:10px 0"><i class="fa-regular fa-face-frown"></i> Không có dữ liệu</div>';return;}c.innerHTML=t.map(x=>`<div class="trending-item"><i class="fa-solid fa-fire"></i><span class="trend-title">${escHtml(x.title)}</span>${x.traffic?`<span class="trend-traffic">${escHtml(x.traffic)}</span>`:''}</div>`).join('');}

// ===== FINANCE =====
function renderFinance(finance){
    const grid=document.getElementById('finance-grid'),tab=state.financeTab;
    if(!finance){grid.innerHTML='<div style="color:var(--text-muted);font-size:.78rem;padding:20px;text-align:center;grid-column:1/-1"><i class="fa-regular fa-clock"></i> Đang tải...</div>';return;}

    let allCards=[];
    // indices
    (finance.indices||[]).forEach(idx=>{
        const up=idx.change>=0; const bar=idx.prices?.length?idx.prices.map(v=>Math.max(2,(v-Math.min(...idx.prices))/(Math.max(...idx.prices)-Math.min(...idx.prices)||1)*22)):[];
        allCards.push({tab:'indices',html:`<div class="finance-card"><div class="finance-symbol"><i class="fa-solid fa-building-columns"></i> ${idx.symbol}</div><div class="finance-price">${numFormat(idx.price)}</div><div class="finance-change ${up?'up':'down'}"><i class="fa-solid fa-${up?'caret-up':'caret-down'}"></i> ${up?'+':''}${numFormat(idx.change)} (${up?'+':''}${idx.changePercent}%)</div><div class="finance-name">Chứng khoán</div>${bar.length>1?`<div class="finance-mini">${bar.map(h=>`<div class="bar" style="height:${h}px;background:${up?'var(--accent-green)':'var(--accent-red)'};opacity:${.3+(h/22)*.7}"></div>`).join('')}</div>`:''}</div>`});
    });
    // gold
    (finance.gold||[]).slice(0,4).forEach(g=>{
        const b=g.buy||g.mua||0,s=g.sell||g.ban||0;
        allCards.push({tab:'gold',html:`<div class="finance-card"><div class="finance-symbol"><i class="fa-solid fa-coins"></i> ${g.name||'Vàng'}</div><div class="finance-price" style="color:var(--accent-yellow)">${numFormat(b)}</div><div class="finance-change" style="color:var(--text-muted);font-size:.72rem">Mua ${numFormat(b)} • Bán ${numFormat(s)}</div><div class="finance-name">Giá vàng</div></div>`});
    });
    // commodities
    (finance.commodities||[]).forEach(c=>{
        const up=c.change>=0; const bar=c.prices?.length?c.prices.map(v=>Math.max(2,(v-Math.min(...c.prices))/(Math.max(...c.prices)-Math.min(...c.prices)||1)*22)):[];
        allCards.push({tab:'commodities',html:`<div class="finance-card"><div class="finance-symbol"><i class="fa-solid fa-oil-can"></i> ${c.symbol}</div><div class="finance-price">${numFormat(c.price)}</div><div class="finance-change ${up?'up':'down'}"><i class="fa-solid fa-${up?'caret-up':'caret-down'}"></i> ${up?'+':''}${numFormat(c.change)} (${up?'+':''}${c.changePercent}%)</div><div class="finance-name">${c.name||'Hàng hoá'}</div>${bar.length>1?`<div class="finance-mini">${bar.map(h=>`<div class="bar" style="height:${h}px;background:${up?'var(--accent-green)':'var(--accent-red)'};opacity:${.3+(h/22)*.7}"></div>`).join('')}</div>`:''}</div>`});
    });
    // petrol
    (finance.petrol||[]).forEach(p=>{
        allCards.push({tab:'petrol',html:`<div class="finance-card"><div class="finance-symbol"><i class="fa-solid fa-gas-pump"></i> ${escHtml(p.name)}</div><div class="finance-price" style="color:var(--accent-cyan)">${numFormat(p.price)}</div><div class="finance-name">Xăng dầu</div></div>`});
    });
    // currency
    (finance.currency||[]).forEach(c=>{
        const up=c.change>=0;
        allCards.push({tab:'currency',html:`<div class="finance-card"><div class="finance-symbol"><i class="fa-solid fa-money-bill-transfer"></i> ${c.symbol}</div><div class="finance-price">${numFormat(c.price)}</div><div class="finance-change ${up?'up':'down'}"><i class="fa-solid fa-${up?'caret-up':'caret-down'}"></i> ${up?'+':''}${numFormat(c.change)} (${up?'+':''}${c.changePercent}%)</div><div class="finance-name">Ngoại tệ</div></div>`});
    });

    const filtered=tab==='all'?allCards:allCards.filter(x=>x.tab===tab);
    grid.innerHTML=filtered.length?filtered.map(x=>x.html).join(''):'<div style="color:var(--text-muted);font-size:.78rem;padding:20px;text-align:center;grid-column:1/-1">Không có dữ liệu</div>';
}

// ===== FINANCE TABS =====
document.addEventListener('DOMContentLoaded',()=>{
    document.querySelectorAll('.ftab').forEach(btn=>{
        btn.addEventListener('click',()=>{
            document.querySelectorAll('.ftab').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            state.financeTab=btn.dataset.tab;
            if(allData)renderFinance(allData.finance);
        });
    });
});

// ===== CHARTS =====
function renderTimelineChart(d){
    if(!d||!d.length)return;const ctx=document.getElementById('chart-timeline')?.getContext('2d');if(!ctx)return;
    if(charts.timeline)charts.timeline.destroy();
    charts.timeline=new Chart(ctx,{type:'line',data:{labels:d.map(x=>x.label),datasets:[{label:'Bài viết',data:d.map(x=>x.count),borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,.08)',fill:true,tension:.3,pointRadius:2,pointHoverRadius:5,borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{...cd(),ticks:{...cd().ticks,maxTicksLimit:8},grid:{display:false}},y:{...cd(),beginAtZero:true,ticks:{...cd().ticks,precision:0}}}}});
}
function renderCategoryChart(st){
    const ctx=document.getElementById('chart-category')?.getContext('2d');if(!ctx)return;
    if(charts.category)charts.category.destroy();
    const e=Object.entries(st).sort((a,b)=>b[1]-a[1]);
    charts.category=new Chart(ctx,{type:'doughnut',data:{labels:e.map(x=>x[0]),datasets:[{data:e.map(x=>x[1]),backgroundColor:CATEGORY_COLORS.slice(0,e.length),borderColor:cssVar('--bg-card','#111317'),borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,cutout:'62%',plugins:{legend:{position:'bottom',labels:{color:cssVar('--chart-text','#8b8f97'),font:{size:9,family:'Inter'},padding:8,boxWidth:10,boxHeight:10}}}}});
}
function renderKeywordChart(kw){
    const ctx=document.getElementById('chart-keywords')?.getContext('2d');if(!ctx)return;
    if(charts.keywords)charts.keywords.destroy();
    const e=Object.entries(kw).slice(0,10);
    charts.keywords=new Chart(ctx,{type:'bar',data:{labels:e.map(x=>x[0]),datasets:[{label:'Xuất hiện',data:e.map(x=>x[1]),backgroundColor:'#a855f7',borderRadius:3,borderSkipped:false}]},options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{...cd(),beginAtZero:true,ticks:{...cd().ticks,precision:0}},y:{...cd(),ticks:{...cd().ticks,font:{size:9}},grid:{display:false}}}}});
}
function renderSourceChart(st){
    const ctx=document.getElementById('chart-source')?.getContext('2d');if(!ctx)return;
    if(charts.source)charts.source.destroy();
    const e=Object.entries(st).sort((a,b)=>b[1]-a[1]).slice(0,8);if(!e.length)return;
    charts.source=new Chart(ctx,{type:'bar',data:{labels:e.map(x=>x[0]),datasets:[{label:'Bài viết',data:e.map(x=>x[1]),backgroundColor:e.map(x=>SOURCE_COLORS[x[0]]||'#5c5f66'),borderRadius:3,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{...cd(true),ticks:{...cd(true).ticks,font:{size:7,maxRotation:30}},grid:{display:false}},y:{...cd(true),beginAtZero:true,ticks:{...cd(true).ticks,precision:0}}}}});
}
function renderCompareChart(st){
    const ctx=document.getElementById('chart-compare')?.getContext('2d');if(!ctx)return;
    if(charts.compare)charts.compare.destroy();
    const e=Object.entries(st).sort((a,b)=>b[1]-a[1]);if(!e.length)return;
    charts.compare=new Chart(ctx,{type:'polarArea',data:{labels:e.map(x=>x[0]),datasets:[{data:e.map(x=>x[1]),backgroundColor:['rgba(59,130,246,.7)','rgba(6,182,212,.7)','rgba(34,197,94,.7)','rgba(249,115,22,.7)','rgba(168,85,247,.7)','rgba(239,68,68,.7)','rgba(234,179,8,.7)','rgba(236,72,153,.7)']}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:cd().color,font:{size:8,family:'Inter'},padding:6,boxWidth:8,boxHeight:8}}},scales:{r:{grid:{color:cd().grid.color},ticks:{display:false,backdropColor:'transparent',color:cd().color}}}}});
}
function renderOverviewChart(st){
    const ctx=document.getElementById('chart-overview')?.getContext('2d');if(!ctx)return;
    if(charts.overview)charts.overview.destroy();
    const e=Object.entries(st).sort((a,b)=>b[1]-a[1]);if(!e.length)return;
    charts.overview=new Chart(ctx,{type:'bar',data:{labels:e.map(x=>x[0]),datasets:[{label:'Bài viết',data:e.map(x=>x[1]),backgroundColor:e.map((_,i)=>CATEGORY_COLORS[i%CATEGORY_COLORS.length]),borderRadius:3,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{...cd(true),ticks:{...cd(true).ticks,font:{size:7,maxRotation:45}},grid:{display:false}},y:{...cd(true),beginAtZero:true,ticks:{...cd(true).ticks,precision:0}}}}});
}
function renderHourlyChart(d){
    const ctx=document.getElementById('chart-hourly')?.getContext('2d');if(!ctx)return;
    if(charts.hourly)charts.hourly.destroy();
    if(!d||!d.length)return;
    charts.hourly=new Chart(ctx,{type:'bar',data:{labels:d.map(x=>x.label),datasets:[{label:'Bài viết',data:d.map(x=>x.count),backgroundColor:'rgba(6,182,212,.6)',borderColor:'#06b6d4',borderWidth:1,borderRadius:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{...cd(true),ticks:{...cd(true).ticks,font:{size:8},maxTicksLimit:12},grid:{display:false}},y:{...cd(true),beginAtZero:true,ticks:{...cd(true).ticks,precision:0}}}}});
}

function renderAllCharts(data){
    renderTimelineChart(data.timeline);renderCategoryChart(data.category_stats);
    renderKeywordChart(data.top_keywords);renderSourceChart(data.source_stats);
    renderCompareChart(data.category_stats);renderOverviewChart(data.category_stats);
    renderHourlyChart(data.timeline);
}

function updateTimestamp(ts){if(ts)document.getElementById('updated-at').innerHTML='<i class="fa-regular fa-clock"></i> '+new Date(ts*1000).toLocaleString('vi-VN');}

// ===== API =====
async function fetchData(action='all'){try{const loc=userCity?`&location=${encodeURIComponent(userCity)}`:'';const controller=new AbortController();const timer=setTimeout(()=>controller.abort(),10000);const r=await fetch(`${API_BASE}?action=${action}${loc}`,{cache:'no-store',signal:controller.signal});clearTimeout(timer);if(!r.ok)throw Error('HTTP '+r.status);return await r.json();}catch(e){console.error('Fetch:',e);return null;}}

async function loadDashboard(){
    const data=await fetchData('all');
    if(!data){
        document.querySelector('.ticker-track').innerHTML='<span class="ticker-placeholder">Không thể kết nối máy chủ. Vui lòng thử lại sau.</span>';
        document.querySelector('#total-articles').textContent='0';
        return;
    }
    if(!data.articles||!data.articles.length){
        document.querySelector('.ticker-track').innerHTML='<span class="ticker-placeholder">Đang chờ dữ liệu từ cronjob... Tự động làm mới sau 60s.</span>';
    }
    allData=data;state.articles=data.articles||[];
    renderStats(data);renderBreaking(data.breaking);renderTicker(data.articles);
    initFilters(data);applyFilters();
    renderCategories(data.category_stats);renderSources(data.source_stats);
    renderKeywords(data.top_keywords);renderTrending(data.trends);
    renderWeather(data.weather);
    renderFinance(data.finance);
    renderAllCharts(data);
    updateTimestamp(data.updated_at);
    setTimeout(startBreakingScroll,500);
}

async function handleRefresh(){
    const btn=document.querySelector('.btn-refresh');
    btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang tải...';btn.disabled=true;
    const data=await fetchData('refresh');
    if(data){
        allData=data;state.articles=data.articles||[];
        renderStats(data);renderBreaking(data.breaking);renderTicker(data.articles);
        initFilters(data);applyFilters();
        renderCategories(data.category_stats);renderSources(data.source_stats);
        renderKeywords(data.top_keywords);renderTrending(data.trends);
        renderWeather(data.weather);
        renderFinance(data.finance);
        renderAllCharts(data);
        updateTimestamp(data.updated_at);
    }
    btn.innerHTML='<i class="fa-solid fa-rotate"></i> Làm mới';btn.disabled=false;
}

// ===== COUNTDOWN =====
let countdownVal = 60;
function startCountdown() {
    countdownVal = Math.floor(POLL_INTERVAL / 1000);
    document.getElementById('countdown-value').textContent = countdownVal;
    setInterval(() => {
        countdownVal--;
        if (countdownVal <= 0) countdownVal = Math.floor(POLL_INTERVAL / 1000);
        document.getElementById('countdown-value').textContent = countdownVal;
    }, 1000);
}

// ===== WEATHER =====
function renderWeather(weather) {
    const row = document.getElementById('weather-row');
    if (!weather || !weather.length) { row.innerHTML = ''; return; }
    row.innerHTML = weather.map(c => {
        const isUser = c.user_location;
        const icon = c.icon ? `<img src="${c.icon}" alt="${escHtml(c.desc)}">` : `<i class="fa-solid fa-${getWeatherIcon(c.code)}"></i>`;
        const fc = (c.forecast || []).slice(0,4).map(f => `<div class="weather-fc"><div class="fc-day">${f.date ? f.date.slice(5) : ''}</div><div class="fc-temp">${f.max}°</div></div>`).join('');
        return `<div class="weather-card${isUser?' user-location':''}">
            <div class="weather-icon">${icon}</div>
            <div class="weather-info">
                <div class="weather-city">${escHtml(c.city)}</div>
                <div class="weather-temp">${c.temp}°C</div>
                <div class="weather-desc">${escHtml(c.desc)}</div>
                <div class="weather-extra"><span><i class="fa-solid fa-droplet"></i> ${c.humidity}%</span><span><i class="fa-solid fa-wind"></i> ${c.wind} km/h</span></div>
                ${fc ? `<div class="weather-forecast">${fc}</div>` : ''}
            </div>
        </div>`;
    }).join('');
}

function getWeatherIcon(code) {
    const map = { '113':'sun','116':'cloud-sun','119':'cloud','122':'clouds','143':'smog','176':'cloud-rain','179':'snowflake','182':'snowflake','185':'snowflake','200':'bolt','227':'snowflake','230':'snowflake','248':'smog','260':'smog','263':'cloud-rain','266':'cloud-rain','293':'cloud-rain','296':'cloud-rain','299':'cloud-showers-heavy','302':'cloud-showers-heavy','305':'cloud-showers-heavy','308':'cloud-showers-heavy','311':'cloud-rain','314':'cloud-rain','317':'cloud-rain','320':'cloud-rain','323':'snowflake','326':'snowflake','329':'snowflake','332':'snowflake','335':'snowflake','338':'snowflake','350':'snowflake','353':'cloud-rain','356':'cloud-showers-heavy','359':'cloud-showers-heavy','362':'snowflake','365':'snowflake','368':'snowflake','371':'snowflake','374':'snowflake','377':'snowflake','386':'bolt','389':'bolt','392':'bolt','395':'bolt' };
    return map[code] || 'cloud';
}

// ===== USER LOCATION =====
function detectUserLocation(){
    if (!navigator.geolocation) { loadDashboard(); return; }
    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat=pos.coords.latitude, lng=pos.coords.longitude;
            // Reverse geocode via free Nominatim API
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=vi`)
                .then(r=>r.json())
                .then(d=>{
                    const addr=d?.address;
                    userCity=addr?.city||addr?.town||addr?.county||addr?.state||'';
                    loadDashboard();
                })
                .catch(()=>loadDashboard());
        },
        () => loadDashboard(),
        { timeout: 5000, enableHighAccuracy: false }
    );
}

// ===== INIT =====
document.addEventListener('DOMContentLoaded',()=>{
    detectUserLocation();
    startCountdown();
    document.querySelector('.btn-refresh')?.addEventListener('click',handleRefresh);
    setInterval(loadDashboard,POLL_INTERVAL);
});
