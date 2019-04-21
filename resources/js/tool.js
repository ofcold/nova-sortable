import Draggable from 'vuedraggable'
import ResourceTable from './components/ResourceTable'
import ResourceTableRow from './components/ResourceTableRow'

Nova.booting((Vue, router, store) => {

	Vue.component('draggable', Draggable)
	Vue.component('resource-table', ResourceTable)
	Vue.component('resource-table-row', ResourceTableRow)
console.log(Vue)
})
