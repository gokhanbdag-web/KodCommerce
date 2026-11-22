// Pure layout operations shared by the editor and Node regression tests.
export function moveColumn(layout, row, from, to) {
 const columns = layout[row]?.columns;
 if (!columns || !Number.isInteger(from) || !Number.isInteger(to) || from < 0 || to < 0 || from >= columns.length || to >= columns.length || from === to) return false;
 const [column] = columns.splice(from, 1);
 columns.splice(to, 0, column);
 return true;
}
export function moveWidget(layout, fromRow, fromColumn, fromIndex, toRow, toColumn, toIndex) {
 const source = layout[fromRow]?.columns[fromColumn]?.widgets;
 const target = layout[toRow]?.columns[toColumn]?.widgets;
 if (!source || !target || ![fromIndex, toIndex].every(Number.isInteger) || fromIndex < 0 || fromIndex >= source.length || toIndex < 0 || toIndex > target.length) return false;
 if (source !== target && target.length >= 30) return false;
 if (source === target && (fromIndex === toIndex || fromIndex + 1 === toIndex)) return false;
 const [widget] = source.splice(fromIndex, 1);
 target.splice(source === target && toIndex > fromIndex ? toIndex - 1 : toIndex, 0, widget);
 return true;
}
