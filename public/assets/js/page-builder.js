import { moveColumn, moveWidget } from './page-layout.mjs';
const translate = (key) => {
 const root = document.querySelector('#pageBuilder');
 if (!root) return key;
 try { return JSON.parse(root.dataset.translations || '{}')[key] || key; }
 catch (_) { return key; }
};
const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let layout = [];
let loaded = false;
let dirty = false;
let initialTitle = '';
let initialSlug = '';
const slugify = value => value.toLocaleLowerCase('en-US').normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0,190);
function markDirty() { dirty = true; const el = document.querySelector('#builderDirty'); if (el) el.hidden = false; }
function markClean() { dirty = false; const el = document.querySelector('#builderDirty'); if (el) el.hidden = true; }

const widths = ['contained', 'fluid', 'full'];
const types = ['heading', 'text', 'button'];
const options = (values, current) => values.map(v => `<option value="${v}" ${v === current ? 'selected' : ''}>${escapeHtml(translate(v))}</option>`).join('');
const widgetHtml = (w, r, c, i) => `<div class="border rounded p-2 mb-2 bg-body-tertiary" draggable="true" data-widget="${i}" data-row="${r}" data-col="${c}">
 <div class="d-flex flex-wrap gap-1 align-items-center mb-2"><span class="small text-secondary me-auto" title="${escapeHtml(translate('Drag to another column'))}">☰ ${translate('Widget')} ${i+1}</span><button class="btn btn-sm btn-outline-secondary" data-action="widget-up" data-r="${r}" data-c="${c}" data-i="${i}">↑</button><button class="btn btn-sm btn-outline-secondary" data-action="widget-down" data-r="${r}" data-c="${c}" data-i="${i}">↓</button><button class="btn btn-sm btn-outline-secondary" data-action="widget-duplicate" data-r="${r}" data-c="${c}" data-i="${i}" title="${escapeHtml(translate('Duplicate widget'))}">⧉</button><button class="btn btn-sm btn-outline-danger" data-action="widget-remove" data-r="${r}" data-c="${c}" data-i="${i}">×</button></div>
 <label class="form-label small">${translate('Type')} <select class="form-select form-select-sm" data-field="widget-type" data-r="${r}" data-c="${c}" data-i="${i}">${options(types,w.type)}</select></label>
 <label class="form-label small d-block">${translate('Content')} <textarea class="form-control form-control-sm" rows="4" maxlength="5000" data-field="widget-text" data-r="${r}" data-c="${c}" data-i="${i}">${escapeHtml(w.text)}</textarea></label>
 </div>`;
const columnHtml = (col,r,c) => `<div class="col-12 col-xl-6"><div class="border rounded p-2 h-100">
 <div class="d-flex flex-wrap gap-1 align-items-center mb-2"><strong class="small me-auto">${translate('Column')} ${c+1}</strong><label class="small">${translate('Span')} <select class="form-select form-select-sm d-inline-block w-auto" data-field="span" data-r="${r}" data-c="${c}">${options(Array.from({length:12},(_,i)=>String(i+1)),String(col.span))}</select></label><button class="btn btn-sm btn-outline-secondary" data-action="column-up" data-r="${r}" data-c="${c}" title="${escapeHtml(translate('Move column left'))}">←</button><button class="btn btn-sm btn-outline-secondary" data-action="column-down" data-r="${r}" data-c="${c}" title="${escapeHtml(translate('Move column right'))}">→</button><button class="btn btn-sm btn-outline-danger" data-action="column-remove" data-r="${r}" data-c="${c}">×</button></div>
 <div class="rounded p-1" data-drop-r="${r}" data-drop-c="${c}" style="min-height:3rem" aria-label="${escapeHtml(translate('Drop widgets here'))}">${col.widgets.map((w,i)=>widgetHtml(w,r,c,i)).join('')}${col.widgets.length ? '' : `<p class="small text-secondary mb-0" data-drop-empty="true">${escapeHtml(translate('Drop widgets here'))}</p>`}</div>
 <div class="d-flex gap-1 flex-wrap"><select class="form-select form-select-sm w-auto" data-new-type-r="${r}" data-new-type-c="${c}">${options(types,'text')}</select><button class="btn btn-sm btn-outline-primary" data-action="widget-add" data-r="${r}" data-c="${c}">${translate('+ Widget')}</button></div>
 </div></div>`;
function render() {
 const root = document.querySelector('#builderRows'); if (!root) return;
 root.innerHTML = layout.map((row,r)=>`<div class="card"><div class="card-header d-flex flex-wrap gap-2 align-items-center">
 <strong class="me-auto">${translate('Row')} ${r+1}</strong><label class="small">${translate('Width')} <select class="form-select form-select-sm d-inline-block w-auto" data-field="width" data-r="${r}">${options(widths,row.width)}</select></label>
 <button class="btn btn-sm btn-outline-secondary" data-action="row-up" data-r="${r}">↑</button><button class="btn btn-sm btn-outline-secondary" data-action="row-down" data-r="${r}">↓</button><button class="btn btn-sm btn-outline-danger" data-action="row-remove" data-r="${r}">${translate('Remove row')}</button>
 </div><div class="card-body"><div class="row g-2">${row.columns.map((col,c)=>columnHtml(col,r,c)).join('')}</div><button class="btn btn-sm btn-outline-primary mt-2" data-action="column-add" data-r="${r}">${translate('+ Column')}</button></div></div>`).join('');
}
function status(message, error=false) { const el=document.querySelector('#builderStatus'); if(el) {el.textContent=message;el.className=error?'mt-3 text-danger':'mt-3 text-success';} }
function move(items,index,delta){const next=index+delta;if(next<0||next>=items.length)return;[items[index],items[next]]=[items[next],items[index]];}
function updateField(el) {
 const r=Number(el.dataset.r),c=Number(el.dataset.c),i=Number(el.dataset.i);
 if(el.dataset.field==='width')layout[r].width=el.value;
 if(el.dataset.field==='span')layout[r].columns[c].span=Number(el.value);
 if(el.dataset.field==='widget-type')layout[r].columns[c].widgets[i].type=el.value;
 if(el.dataset.field==='widget-text')layout[r].columns[c].widgets[i].text=el.value;
}
function act(el) {
 const action=el.dataset.action,r=Number(el.dataset.r),c=Number(el.dataset.c),i=Number(el.dataset.i);
 const row=layout[r],column=row?.columns[c];
 if(action==='row-remove')layout.splice(r,1);
 if(action==='row-up')move(layout,r,-1);
 if(action==='row-down')move(layout,r,1);
 if(action==='column-add'&&row.columns.length<12)row.columns.push({span:6,widgets:[]});
 if(action==='column-remove')row.columns.splice(c,1);
 if(action==='column-up')moveColumn(layout,r,c,c-1);
 if(action==='column-down')moveColumn(layout,r,c,c+1);
 if(action==='widget-add'&&column.widgets.length<30)column.widgets.push({type:document.querySelector(`[data-new-type-r="${r}"][data-new-type-c="${c}"]`).value,text:''});
 if(action==='widget-remove')column.widgets.splice(i,1);
 if(action==='widget-duplicate'&&column.widgets.length<30)column.widgets.splice(i+1,0,{...column.widgets[i]});
 if(action==='widget-up')move(column.widgets,i,-1);
 if(action==='widget-down')move(column.widgets,i,1);
 render();markDirty();
}
function preview() {
 const root=document.querySelector('#builderPreview');root.hidden=false;
 root.innerHTML=`<h2 class="h5">${escapeHtml(translate('Preview (unsaved)'))}</h2>`+layout.map(row=>`<section class="${row.width==='contained'?'container': 'container-fluid'} py-3"><div class="row g-3">${row.columns.map(col=>`<div class="col-12 col-lg-${col.span}">${col.widgets.map(w=>w.type==='heading'?`<h2>${escapeHtml(w.text)}</h2>`:w.type==='button'?`<span class="btn btn-primary">${escapeHtml(w.text)}</span>`:`<p>${escapeHtml(w.text).replace(/\n/g,'<br>')}</p>`).join('')}</div>`).join('')}</div></section>`).join('');
 root.scrollIntoView({behavior:'smooth',block:'nearest'});
}
async function save() {
 const body=new FormData();body.set('id',document.querySelector('#pageBuilder').dataset.pageId||'');body.set('slug',document.querySelector('#builderSlug').value);body.set('status',document.querySelector('#builderPublishStatus').value);body.set('csrf',document.querySelector('meta[name="csrf-token"]').content);body.set('title',document.querySelector('#builderPageTitle').value);body.set('layout',JSON.stringify(layout));
 try {const response=await fetch('/api/pages',{method:'POST',body,credentials:'same-origin'});const result=await response.json();if(response.ok){document.querySelector('#pageBuilder').dataset.pageId=String(result.id);history.replaceState({},'', '/admin/content-management/pages?edit='+result.id);markClean();const link=document.querySelector('#builderViewPage');if(link){link.href='/pages/'+encodeURIComponent(document.querySelector('#builderSlug').value);link.hidden=document.querySelector('#builderPublishStatus').value!=='published';}}status(response.ok?translate('Page saved'):(result.error||translate('Save failed')),!response.ok);}
 catch(e){status(translate('Unable to save homepage'),true);}
}
async function initialize() {
 const root=document.querySelector('#pageBuilder');if(!root||root.dataset.initialized)return;root.dataset.initialized='true';
 try {layout=JSON.parse(root.dataset.layout||'[]');render();initialTitle=document.querySelector('#builderPageTitle').value;initialSlug=document.querySelector('#builderSlug').value;}
 catch(e){status(translate('Unable to load saved layout'),true);}
}
document.addEventListener('click',e=>{
 const root=e.target.closest('#pageBuilder');if(!root)return;
 const action=e.target.closest('[data-builder-action]');if(action){
 if(action.dataset.builderAction==='add-row'&&layout.length<40){layout.push({width:'contained',columns:[{span:12,widgets:[]}]});render();markDirty();}
 if(action.dataset.builderAction==='preview')preview();
 if(action.dataset.builderAction==='save')save();
 return;
 }
 const control=e.target.closest('[data-action]');if(control)act(control);
});
document.addEventListener('change',e=>{if(e.target.closest('#pageBuilder')&&e.target.dataset.field){updateField(e.target);markDirty();}});
document.addEventListener('input',e=>{if(e.target.closest('#pageBuilder')&&e.target.dataset.field==='widget-text'){updateField(e.target);markDirty();}});
let dragging=null;
document.addEventListener('dragstart',e=>{
 const card=e.target.closest('#pageBuilder [data-widget]');
 if(!card)return;
 if(e.target.closest('textarea, input, select, button')){e.preventDefault();return;}
 dragging={r:Number(card.dataset.row),c:Number(card.dataset.col),i:Number(card.dataset.widget)};
 e.dataTransfer.effectAllowed='move';
 e.dataTransfer.setData('text/plain','page-widget');
});
document.addEventListener('dragover',e=>{
 if(!dragging)return;
 const zone=e.target.closest('#pageBuilder [data-drop-r]');
 if(zone){e.preventDefault();e.dataTransfer.dropEffect='move';zone.classList.add('border','border-primary');}
});
document.addEventListener('dragleave',e=>{
 const zone=e.target.closest('#pageBuilder [data-drop-r]');
 if(zone&&!zone.contains(e.relatedTarget))zone.classList.remove('border','border-primary');
});
document.addEventListener('drop',e=>{
 if(!dragging)return;
 const zone=e.target.closest('#pageBuilder [data-drop-r]');
 if(!zone)return;
 e.preventDefault();
 const row=Number(zone.dataset.dropR),col=Number(zone.dataset.dropC);
 const card=e.target.closest('[data-widget]');
 let index=layout[row].columns[col].widgets.length;
 if(card){
  index=Number(card.dataset.widget);
  if(e.clientY>=card.getBoundingClientRect().top+card.getBoundingClientRect().height/2)index++;
 }
 if(moveWidget(layout,dragging.r,dragging.c,dragging.i,row,col,index)){render();markDirty();}
 zone.classList.remove('border','border-primary');
 dragging=null;
});
document.addEventListener('dragend',()=>{
 dragging=null;
 document.querySelectorAll('#pageBuilder [data-drop-r]').forEach(el=>el.classList.remove('border','border-primary'));
});
new MutationObserver(()=>initialize()).observe(document.querySelector('#adminContent'),{childList:true});
initialize();

document.addEventListener('input',e=>{if(!e.target.closest('#pageBuilder'))return;if(e.target.id==='builderPageTitle'){const slug=document.querySelector('#builderSlug');if(slug&&!slug.dataset.manual&&(slug.value===''||slug.value===slugify(initialTitle)))slug.value=slugify(e.target.value);markDirty();}if(e.target.id==='builderSlug'){e.target.dataset.manual='1';markDirty();}});
document.addEventListener('change',e=>{if(e.target.closest('#pageBuilder')&&e.target.id==='builderPublishStatus'){markDirty();}});
window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue='';}});
