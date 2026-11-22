import assert from 'node:assert/strict';
import {moveColumn, moveWidget} from '../public/assets/js/page-layout.mjs';
const layout=[
 {width:'contained',columns:[
  {span:6,widgets:[{type:'heading',text:'A'},{type:'text',text:'B'}]},
  {span:6,widgets:[]}
 ]},
 {width:'fluid',columns:[{span:12,widgets:[{type:'button',text:'C'}]}]}
];
assert.equal(moveWidget(layout,0,0,0,0,1,0),true,'move into empty column');
assert.deepEqual(layout[0].columns[1].widgets.map(w=>w.text),['A']);
assert.deepEqual(layout[0].columns[0].widgets.map(w=>w.text),['B']);
assert.equal(moveWidget(layout,0,1,0,1,0,1),true,'move between rows');
assert.deepEqual(layout[1].columns[0].widgets.map(w=>w.text),['C','A']);
assert.equal(moveWidget(layout,1,0,1,1,0,0),true,'reorder inside column');
assert.deepEqual(layout[1].columns[0].widgets.map(w=>w.text),['A','C']);
assert.equal(moveWidget(layout,1,0,0,1,0,1),false,'no-op drop after same widget');
assert.equal(moveWidget(layout,0,0,0,0,1,3),false,'invalid index does not lose widgets');
assert.equal(moveColumn(layout,0,0,1),true,'reorder columns');
assert.deepEqual(layout[0].columns.map(c=>c.widgets.map(w=>w.text)),[[],['B']]);
assert.equal(moveColumn(layout,0,0,2),false,'out of range');
const full={width:'contained',columns:[{span:12,widgets:Array.from({length:30},(_,i)=>({type:'text',text:String(i)}))}]};
layout.push(full);
assert.equal(moveWidget(layout,0,1,0,2,0,30),false,'max 30 widgets');
assert.equal(layout[0].columns[1].widgets.length,1,'rejected move preserves source');
console.log('Page layout operations passed');
