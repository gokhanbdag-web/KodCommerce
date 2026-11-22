// Pure, in-editor clipboard and undo state (never accesses the OS clipboard).
export const cloneLayout = layout => JSON.parse(JSON.stringify(layout));
export function createHistory(limit = 30) {
 const past = [];
 return {
  capture(layout) { past.push(cloneLayout(layout)); if (past.length > limit) past.shift(); },
  undo(current) { return past.length ? past.pop() : null; },
  clear() { past.length = 0; },
  get canUndo() { return past.length > 0; }
 };
}
export function copyWidget(layout, row, column, index) {
 const widget = layout[row]?.columns[column]?.widgets[index];
 return widget ? cloneLayout(widget) : null;
}
export function pasteWidget(layout, row, column, widget, index = null) {
 const target = layout[row]?.columns[column]?.widgets;
 if (!target || !widget || target.length >= 30) return false;
 const position = index === null ? target.length : index;
 if (!Number.isInteger(position) || position < 0 || position > target.length) return false;
 target.splice(position, 0, cloneLayout(widget));
 return true;
}
