const translate = (key) => {
 const root = document.querySelector('#pageBuilder');
 if (!root) return key;
 try { return JSON.parse(root.dataset.translations || '{}')[key] || key; }
 catch (_) { return key; }
};
const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let layout = [];
let loaded = false;
const widths = ['contained', 'fluid', 'full'];
const types = ['heading', 'text', 'button'];
const options = (values, current) => values.map(v => `<option value="${v}" ${v === current ? 'selected' : ''}>${escapeHtml(translate(v))}</option>`).join('');
const widgetHtml = (w, r, c, i) => `<div class="border rounded p-2 mb-2 bg-body-tertiary" draggable="true" data-widget="${i}" data-row="${r}" data-col="${c}">
 <div class="d-flex flex-wrap gap-1 align-items-center mb-2"><span class="small text-secondary me-auto">☰ ${translate('Widget')} ${i+1}</span><button class="btn btn-sm btn-outline-secondary" data-action="widget-up" data-r="${r}" data-c="${c}" data-i="${i}">↑</button><button class="btn btn-sm btn-outline-secondary" data-action="widget-down" data-r="${r}" data-c="${c}" data-i="${i}">↓</button><button class="btn btn-sm btn-outline-danger" data-action="widget-remove" data-r="${r}" data-c="${c}" data-i="${i}">×</button></div>
 <label class="form-label small">${translate('Type')} <select class="form-select form-select-sm" data-field="widget-type" data-r="${r}" data-c="${c}" data-i="${i}">${options(types,w.type)}</select></label>
 <label class="form-label small d-block">${translate('Content')} <textarea class="form-control form-control-sm" rows="2" maxlength="5000" data-field="widget-text" data-r="${r}" data-c="${c}" data-i="${i}">${escapeHtml(w.text)}</textarea></label>
 </div>`;
const columnHtml = (col,r,c) => `<div class="col-12 col-xl-6"><div class="border rounded p-2 h-100">
 <div class="d-flex flex-wrap gap-1 align-items-center mb-2"><strong class="small me-auto">${translate('Column')} ${c+1}</strong><label class="small">${translate('Span')} <select class="form-select form-select-sm d-inline-block w-auto" data-field="span" data-r="${r}" data-c="${c}">${options(Array.from({length:12},(_,i)=>String(i+1)),String(col.span))}</select></label><button class="btn btn-sm btn-outline-danger" data-action="column-remove" data-r="${r}" data-c="${c}">×</button></div>
 <div data-drop-r="${r}" data-drop-c="${c}">${col.widgets.map((w,i)=>widgetHtml(w,r,c,i)).join('')}</div>
 <div class="d-flex gap-1 flex-wrap"><select class="form-select form-select-sm w-auto" data-new-type-r="${r}" data-new-type-c="${c}">${options(types,'text')}</select><button class="btn btn-sm btn-outline-primary" data-action="widget-add" data-r="${r}" data-c="${c}">${translate('+ Widget')}</button></div>
 </div></div>`;
function render() {
 const root = document.querySelector('#builderRows'); if (!root) return;
 root.innerHTML = layout.map((row,r)=>`<div class="card"><div class="card-header d-flex flex-wrap gap-2 align-items-center">
 <strong class="me-auto">${translate('Row')} ${r+1}</strong><label class="small">${translate('Width')} <select class="form-select form-select-sm d-inline-block w-auto" data-field="width" data-r="${r}">${options(widths,row.width)}</select></label>
 <button class="btn btn-sm btn-outline-secondary" data-action="row-up" data-r="${r}">↑</button><button class="btn btn-sm btn-outline-secondary" data-action="row-down" data-r="${r}">↓</button><button class="btn btn-sm btn-outline-danger" data-action="row-remove" data-r="${r}">${translate('Remove row')}</button>
 </div><div class="card-body"><div class="row g-2">${row.columns.map((col,c)=>columnHtml(col,r,c)).join('')}</div><button class="btn btn-sm btn-outline-primary mt-2" data-action="column-add" data-r="${r}">${translate('+ Column')}</button></div></div>`).join('');
}
function status(message, error=false) { const el=document.querySelector('#builderStatus'); if(el) {el.text${translate('Content')}=message;el.className=error?'mt-3 text-danger':'mt-3 text-success';} }
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
 if(action==='widget-add'&&column.widgets.length<30)column.widgets.push({type:document.querySelector(`[data-new-type-r="${r}"][data-new-type-c="${c}"]`).value,text:''});
 if(action==='widget-remove')column.widgets.splice(i,1);
 if(action==='widget-up')move(column.widgets,i,-1);
 if(action==='widget-down')move(column.widgets,i,1);
 render();
}
function preview() {
 const root=document.querySelector('#builderPreview');root.hidden=false;
 root.innerHTML='<h2 class="h5">${translate('Preview (unsaved)')}</h2>'+layout.map(row=>`<section class="${row.width==='contained'?'container': 'container-fluid'} py-3"><div class="row g-3">${row.columns.map(col=>`<div class="col-12 col-lg-${col.span}">${col.widgets.map(w=>w.type==='heading'?`<h2>${escapeHtml(w.text)}</h2>`:w.type==='button'?`<span class="btn btn-primary">${escapeHtml(w.text)}</span>`:`<p>${escapeHtml(w.text).replace(/\n/g,'<br>')}</p>`).join('')}</div>`).join('')}</div></section>`).join('');
 root.scrollIntoView({behavior:'smooth',block:'nearest'});
}
async function save() {
 const body=new FormData();body.set('csrf',document.querySelector('meta[name="csrf-token"]').content);body.set('title',document.querySelector('#builderPageTitle').value);body.set('layout',JSON.stringify(layout));
 try {const response=await fetch('/api/pages/home',{method:'POST',body,credentials:'same-origin'});const result=await response.json();status(response.ok?translate('Homepage published. View it at /'):(result.error||translate('Save failed')),!response.ok);}
 catch(e){status(translate('Unable to save homepage'),true);}
}
async function initialize() {
 const root=document.querySelector('#pageBuilder');if(!root||root.dataset.initialized)return;root.dataset.initialized='true';
 try {const response=await fetch('/api/pages/home',{credentials:'same-origin'});if(!response.ok)throw Error(translate('Unable to load page'));const data=await response.json();layout=data.layout||[];document.querySelector('#builderPageTitle').value=data.title||'Homepage';loaded=true;render();}
 catch(e){status(translate('Unable to load saved layout'),true);}
}
document.addEventListener('click',e=>{
 const root=e.target.closest('#pageBuilder');if(!root)return;
 const action=e.target.closest('[data-builder-action]');if(action){
 if(action.dataset.builderAction==='add-row'&&layout.length<40){layout.push({width:'contained',columns:[{span:12,widgets:[]}]});render();}
 if(action.dataset.builderAction==='preview')preview();
 if(action.dataset.builderAction==='save')save();
 return;
 }
 const control=e.target.closest('[data-action]');if(control)act(control);
});
document.addEventListener('change',e=>{if(e.target.closest('#pageBuilder')&&e.target.dataset.field)updateField(e.target);});
document.addEventListener('input',e=>{if(e.target.closest('#pageBuilder')&&e.target.dataset.field==='widget-text')updateField(e.target);});
let dragging=null;
document.addEventListener('dragstart',e=>{const card=e.target.closest('#pageBuilder [data-widget]');if(card){dragging={r:Number(card.dataset.row),c:Number(card.dataset.col),i:Number(card.dataset.widget)};e.dataTransfer.effectAllowed='move';}});
document.addEventListener('dragover',e=>{if(dragging&&e.target.closest('#pageBuilder [data-drop-r]'))e.preventDefault();});
document.addEventListener('drop',e=>{const target=e.target.closest('#pageBuilder [data-widget]');if(!dragging||!target)return;e.preventDefault();const r=Number(target.dataset.row),c=Number(target.dataset.col),i=Number(target.dataset.widget);if(dragging.r===r&&dragging.c===c){move(layout[r].columns[c].widgets,dragging.i,i-dragging.i);render();}dragging=null;});
document.addEventListener('dragend',()=>dragging=null);
new MutationObserver(()=>initialize()).observe(document.querySelector('#admin${translate('Content')}'),{childList:true});
initialize();
