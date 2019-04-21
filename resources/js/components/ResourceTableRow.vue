<template>
	<tr :dusk="resource['id'].value + '-row'">
		<!-- Resource Selection Checkbox -->
		<td
			:class="{
				'w-16': shouldShowCheckboxes,
				'w-8': !shouldShowCheckboxes,
			}"
		>
			<div class="flex items-center">
				<checkbox
					:data-testid="`${testId}-checkbox`"
					:dusk="`${resource['id'].value}-checkbox`"
					v-if="shouldShowCheckboxes"
					:checked="checked"
					@input="toggleSelection"
				/>

				<svg v-if="resource.sortable"
					xmlns="http://www.w3.org/2000/svg"
					width="16"
					height="16"
					viewBox="0 0 512 512"
					aria-labelledby="arrows"
					role="presentation"
					class="fill-current ml-4 handle cursor-move">
					<path d="M337.782 434.704l-73.297 73.782c-4.686 4.686-12.284 4.686-16.971 0l-73.296-73.782c-4.686-4.686-4.686-12.284 0-16.97l7.07-7.07c4.686-4.686 12.284-4.686 16.971 0L239 451.887h1V272H60.113v1l41.224 40.741c4.686 4.686 4.686 12.284 0 16.971l-7.071 7.07c-4.686 4.686-12.284 4.686-16.97 0L3.515 264.485c-4.686-4.686-4.686-12.284 0-16.971l73.782-73.297c4.686-4.686 12.284-4.686 16.971 0l7.071 7.071c4.686 4.686 4.686 12.284 0 16.971L60.113 239v1H240V60.113h-1l-40.741 41.224c-4.686 4.686-12.284 4.686-16.971 0l-7.07-7.071c-4.686-4.686-4.687-12.284 0-16.97l73.297-73.782c4.686-4.686 12.284-4.686 16.971 0l73.297 73.782c4.686 4.686 4.686 12.284 0 16.971l-7.071 7.071c-4.686 4.686-12.284 4.686-16.971 0L273 60.113h-1V240h179.887v-1l-41.224-40.741c-4.686-4.686-4.686-12.284 0-16.971l7.071-7.07c4.686-4.686 12.284-4.686 16.97 0l73.782 73.297c4.687 4.686 4.686 12.284 0 16.971l-73.782 73.297c-4.686 4.686-12.284 4.686-16.97 0l-7.071-7.07c-4.686-4.686-4.686-12.284 0-16.971L451.887 273v-1H272v179.887h1l40.741-41.224c4.686-4.686 12.284-4.686 16.971 0l7.07 7.071c4.686 4.685 4.686 12.283 0 16.97z"/>
				</svg>
			</div>
		</td>

		<!-- Fields -->
		<td v-for="field in resource.fields">
			<component
				:is="'index-' + field.component"
				:class="`text-${field.textAlign}`"
				:resource-name="resourceName"
				:via-resource="viaResource"
				:via-resource-id="viaResourceId"
				:field="field"
			/>
		</td>

		<td class="td-fit text-right pr-6">
			<!-- View Resource Link -->
			<span v-if="resource.authorizedToView">
				<router-link
					:data-testid="`${testId}-view-button`"
					:dusk="`${resource['id'].value}-view-button`"
					class="cursor-pointer text-70 hover:text-primary mr-3"
					:to="{
						name: 'detail',
						params: {
							resourceName: resourceName,
							resourceId: resource['id'].value,
						},
					}"
					:title="__('View')"
				>
					<icon type="view" width="22" height="18" view-box="0 0 22 16" />
				</router-link>
			</span>

			<span v-if="resource.authorizedToUpdate">
				<!-- Edit Pivot Button -->
				<router-link
					v-if="relationshipType == 'belongsToMany' || relationshipType == 'morphToMany'"
					class="cursor-pointer text-70 hover:text-primary mr-3"
					:dusk="`${resource['id'].value}-edit-attached-button`"
					:to="{
						name: 'edit-attached',
						params: {
							resourceName: viaResource,
							resourceId: viaResourceId,
							relatedResourceName: resourceName,
							relatedResourceId: resource['id'].value,
						},
						query: {
							viaRelationship: viaRelationship,
						},
					}"
					:title="__('Edit Attached')"
				>
					<icon type="edit" />
				</router-link>

				<!-- Edit Resource Link -->
				<router-link
					v-else
					class="cursor-pointer text-70 hover:text-primary mr-3"
					:dusk="`${resource['id'].value}-edit-button`"
					:to="{
						name: 'edit',
						params: {
							resourceName: resourceName,
							resourceId: resource['id'].value,
						},
						query: {
							viaResource: viaResource,
							viaResourceId: viaResourceId,
							viaRelationship: viaRelationship,
						},
					}"
					:title="__('Edit')"
				>
					<icon type="edit" />
				</router-link>
			</span>

			<!-- Delete Resource Link -->
			<button
				:data-testid="`${testId}-delete-button`"
				:dusk="`${resource['id'].value}-delete-button`"
				class="appearance-none cursor-pointer text-70 hover:text-primary mr-3"
				v-if="resource.authorizedToDelete && (!resource.softDeleted || viaManyToMany)"
				@click.prevent="openDeleteModal"
				:title="__(viaManyToMany ? 'Detach' : 'Delete')"
			>
				<icon />
			</button>

			<!-- Restore Resource Link -->
			<button
				:dusk="`${resource['id'].value}-restore-button`"
				class="appearance-none cursor-pointer text-70 hover:text-primary mr-3"
				v-if="resource.authorizedToRestore && resource.softDeleted && !viaManyToMany"
				@click.prevent="openRestoreModal"
				:title="__('Restore')"
			>
				<icon type="restore" with="20" height="21" />
			</button>

			<portal to="modals">
				<transition name="fade">
					<delete-resource-modal
						v-if="deleteModalOpen"
						@confirm="confirmDelete"
						@close="closeDeleteModal"
						:mode="viaManyToMany ? 'detach' : 'delete'"
					>
						<div slot-scope="{ uppercaseMode, mode }" class="p-8">
							<heading :level="2" class="mb-6">{{
								__(uppercaseMode + ' Resource')
							}}</heading>
							<p class="text-80 leading-normal">
								{{ __('Are you sure you want to ' + mode + ' this resource?') }}
							</p>
						</div>
					</delete-resource-modal>
				</transition>

				<transition name="fade">
					<restore-resource-modal
						v-if="restoreModalOpen"
						@confirm="confirmRestore"
						@close="closeRestoreModal"
					>
						<div class="p-8">
							<heading :level="2" class="mb-6">{{ __('Restore Resource') }}</heading>
							<p class="text-80 leading-normal">
								{{ __('Are you sure you want to restore this resource?') }}
							</p>
						</div>
					</restore-resource-modal>
				</transition>
			</portal>
		</td>
	</tr>
</template>

<script>
export default {
	props: [
		'testId',
		'deleteResource',
		'restoreResource',
		'resource',
		'resourcesSelected',
		'resourceName',
		'relationshipType',
		'viaRelationship',
		'viaResource',
		'viaResourceId',
		'viaManyToMany',
		'checked',
		'actionsAreAvailable',
		'shouldShowCheckboxes',
		'updateSelectionStatus',
	],

	data: () => ({
		deleteModalOpen: false,
		restoreModalOpen: false,
	}),

	methods: {
		/**
		 * Select the resource in the parent component
		 */
		toggleSelection() {
			this.updateSelectionStatus(this.resource)
		},

		openDeleteModal() {
			this.deleteModalOpen = true
		},

		confirmDelete() {
			this.deleteResource(this.resource)
			this.closeDeleteModal()
		},

		closeDeleteModal() {
			this.deleteModalOpen = false
		},

		openRestoreModal() {
			this.restoreModalOpen = true
		},

		confirmRestore() {
			this.restoreResource(this.resource)
			this.closeRestoreModal()
		},

		closeRestoreModal() {
			this.restoreModalOpen = false
		},
	},
}
</script>
