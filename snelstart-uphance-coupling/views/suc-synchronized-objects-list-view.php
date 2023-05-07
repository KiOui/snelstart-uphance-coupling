<?php

include_once SUC_ABSPATH . 'includes/synchronizers/class-sucsynchronizer.php';

$paged = isset( $_GET['paged'] ) ? intval( wp_unslash( $_GET['paged'] ) ) : 1;
if ( $paged <= 0 ) {
	$paged = 1;
}

$per_page = isset( $_GET['per_page'] ) ? intval( wp_unslash( $_GET['per_page'] ) ) : 20;
if ( $per_page <= 0 ) {
	$per_page = 20;
}

$filter_succeeded = isset( $_GET['succeeded'] ) ? strval( wp_unslash( $_GET['succeeded'] ) ) : null;
if ( 'true' !== $filter_succeeded && 'false' !== $filter_succeeded ) {
	$filter_succeeded = null;
}

$types = array_keys( SUCSynchronizer::$synchronizer_classes );

$filter_type = isset( $_GET['type'] ) ? strval( wp_unslash( $_GET['type'] ) ) : null;
if ( ! in_array( $filter_type, $types ) ) {
	$filter_type = null;
}

$filter_source = isset( $_GET['source'] ) ? strval( wp_unslash( $_GET['source'] ) ) : null;
if ( 'webhook' !== $filter_source && 'cron' !== $filter_source ) {
	$filter_source = null;
}

$filter_method = isset( $_GET['method'] ) ? strval( wp_unslash( $_GET['method'] ) ) : null;
if ( 'create' !== $filter_method && 'update' !== $filter_method && 'delete' !== $filter_method ) {
	$filter_method = null;
}

$nonce = wp_create_nonce( 'wp_rest' );

?>

<div class="suc-synchronized-objects-list wrap">
	<h1 class="wp-heading-inline mb-2">Synchronized Uphance objects</h1>
	<div id="synchronized-objects-list">
		<div class="mb-2">
			<input type="text" class="ml-auto me-1" id="search-input" placeholder="Search Object ID"/>
			<select class="filter-object-type input-mini me-1" title="Filter object type" v-model="filter_type">
				<option value="">Filter object type</option>
				<?php
				foreach ( $types as $type ) {
					?>
						<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></option>
						<?php
				}
				?>
			</select>
			<select class="filter-succeeded input-mini me-1" title="Filter succeeded" v-model="filter_succeeded">
				<option value="">Filter succeeded</option>
				<option value="true">
					Succeeded
				</option>
				<option value="false">
					Not succeeded
				</option>
			</select>
			<select class="filter-source input-mini me-1" title="Filter source" v-model="filter_source">
				<option value="">Filter source</option>
				<option value="webhook">
					Webhook
				</option>
				<option value="cron">
					Cron
				</option>
			</select>
			<select class="filter-method input-mini me-1" title="Filter method" v-model="filter_method">
				<option value="">Filter method</option>
				<option value="create">
					Create
				</option>
				<option value="update">
					Update
				</option>
				<option value="delete">
					Delete
				</option>
			</select>
		</div>
		<div class="table-wrapper">
			<table class="wp-list-table widefat w-100 striped table-view-list">
				<thead>
					<tr>
						<th>Object ID</th>
						<th>Object type</th>
						<th>Source</th>
						<th>Method</th>
						<th>Date</th>
						<th>Object URL</th>
						<th>Succeeded</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody v-if="synchronized_objects.length === 0">
					<tr>
						<td colspan="8">
							<p class="alert alert-warning">
								There are no synchronized objects stored in the database.
							</p>
						</td>
					</tr>
				</tbody>
				<tbody v-else>
					<tr v-for="(object, index) of synchronized_objects" :key="`synchronized_object_${object.id}`">
						<td>
							{{ object.meta.id }}
						</td>
						<td>
							{{ object.meta.type }}
						</td>
						<td>
							{{ object.meta.source }}
						</td>
						<td>
							{{ object.meta.method }}
						</td>
						<td>
							{{ new Date(object.date).toISOString() }}
						</td>
						<td>
							<template v-if="object.meta.url">
								<a :href="object.meta.url" target="_blank">{{ object.meta.url }}</a>
							</template>
						</td>
						<td>
							<i v-if="object.meta.succeeded" class="fs-4 text-success fa-solid fa-check"></i>
							<i v-else class="fs-4 text-danger fa-solid fa-xmark"></i>
						</td>
						<td>
							<button type="button" class="button action" data-bs-toggle="modal" :data-bs-target="`#details-modal-${index}`">Details</button>
						</td>
					</tr>
				</tbody>
				<tfoot v-if="synchronized_objects.length > 0">
					<tr>
						<th colspan="2" class="ts-pager">
							<button v-if="page === 1" type="button" class="btn first disabled"><i class="fa-solid fa-backward-fast"></i></button>
							<button v-else v-on:click="update_page(1);" type="button" class="btn first"><i class="fa-solid fa-backward-fast"></i></button>

							<button v-if="page === 1" type="button" class="btn prev disabled"><i class="fa-solid fa-backward"></i></button>
							<button v-else v-on:click="update_page(page - 1);" type="button" class="btn prev"><i class="fa-solid fa-backward"></i></button>

							<button v-if="page === amount_of_pages" type="button" class="btn next disabled"><i class="fa-solid fa-forward"></i></button>
							<button v-else v-on:click="update_page(page + 1);" type="button" class="btn next"><i class="fa-solid fa-forward"></i></button>

							<button v-if="page === amount_of_pages" type="button" class="btn last disabled me-1"><i class="fa-solid fa-forward-fast"></i></button>
							<button v-else v-on:click="update_page(amount_of_pages);" type="button" class="btn last me-1"><i class="fa-solid fa-forward-fast"></i></button>

							<select class="pagesize input-mini me-1" title="Select page size" v-model="per_page">
								<option value="10">10</option>
								<option value="20">20</option>
								<option value="50">50</option>
								<option value="100">100</option>
							</select>
							<select class="pagenum input-mini" title="Select page number" v-model="page">
								<template v-for="index in amount_of_pages" :key="index">
									<option :value="index">{{ index }}</option>
								</template>
							</select>
						</th>
						<th colspan="6"></th>
					</tr>
				</tfoot>
			</table>
		</div>
		<div v-for="(object, index) of synchronized_objects" :key="`synchronized_object_${object.id}`" class="modal" :id="`details-modal-${index}`" tabindex="-1" role="dialog" :aria-labelledby="`details-modal-${index}-label`" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">#{{ object.meta.id }}</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<table class="wp-list-table widefat fixed striped table-view-list posts mb-3">
							<thead>
								<tr>
									<td>Property</td>
									<td>Value</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>ID</td>
									<td>{{ object.meta.id }}</td>
								</tr>
								<tr>
									<td>Type</td>
									<td>{{ object.meta.type }}</td>
								</tr>
								<tr>
									<td>Date</td>
									<td>{{ new Date(object.date).toISOString() }}</td>
								</tr>
								<tr>
									<td>URL</td>
									<td>
										<template v-if="object.meta.url">
											<a :href="object.meta.url" target="_blank">{{ object.meta.url }}</a>
										</template>
									</td>
								</tr>
								<tr>
									<td>Succeeded</td>
									<td>
										<i v-if="object.meta.succeeded" class="fs-4 text-success fa-solid fa-check"></i>
										<i v-else class="fs-4 text-danger fa-solid fa-xmark"></i>
									</td>
								</tr>
								<tr>
									<td>Source</td>
									<td>{{ object.meta.source }}</td>
								</tr>
								<tr>
									<td>Method</td>
									<td>{{ object.meta.method }}</td>
								</tr>
								<template v-if="Object.keys(object.meta.extra_data).length > 0">
									<tr v-for="[key, value] of Object.entries(object.meta.extra_data)">
										<td>
											{{ key }}
										</td>
										<td>
											{{ value }}
										</td>
									</tr>
								</template>
							</tbody>
						</table>
						<div v-if="object.meta.error_message">
							<p class="alert alert-danger">
								{{ object.meta.error_message }}
							</p>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button v-if="object.meta.type in retries_in_progress && retries_in_progress[object.meta.type].indexOf(object.meta.id) !== -1" type="button" class="btn btn-danger disabled d-flex align-center justify-content-center"><span class="me-1">Retry Synchronization</span> <span class="loader"></span></button>
						<button v-else type="button" class="btn btn-danger" @click="retry_synchronization(object.meta.id, object.meta.type)">Retry Synchronization</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	const { createApp } = Vue;

	createApp({
		data() {
			return {
				page: <?php echo esc_js( $paged ); ?>,
				per_page: <?php echo esc_js( $per_page ); ?>,
				filter_succeeded: "<?php echo esc_js( $filter_succeeded ); ?>",
				filter_type: "<?php echo esc_js( $filter_type ); ?>",
				filter_source: "<?php echo esc_js( $filter_source ); ?>",
				filter_method: "<?php echo esc_js( $filter_method ); ?>",
				synchronized_objects: [],
				retries_in_progress: {},
				amount_of_objects: 0,
				amount_of_pages: 0,
			}
		},
		computed: {

		},
		created() {
			this.refresh_objects();
		},
		watch: {
			page: {
				handler(val, oldVal) {
					if (this.page <= 0) {
						this.page = 1;
					}
					if (this.page > this.amount_of_pages) {
						this.page = this.amount_of_pages;
					}
					this.refresh_objects();
					this.set_query_parameters();
				}
			},
			per_page: {
				handler(val, oldVal) {
					if (this.per_page <= 0) {
						this.per_page = 20;
					}
					this.page = 1;
					this.refresh_objects();
					this.set_query_parameters();
				}
			},
			filter_succeeded: {
				handler(val, oldVal) {
					this.page = 1;
					this.refresh_objects();
					this.set_query_parameters();
				}
			},
			filter_type: {
				handler(val, oldVal) {
					this.page = 1;
					this.refresh_objects();
					this.set_query_parameters();
				}
			},
			filter_source: {
				handler(val, oldVal) {
					this.page = 1;
					this.refresh_objects();
					this.set_query_parameters();
				}
			},
			filter_method: {
				handler(val, oldVal) {
					this.page = 1;
					this.refresh_objects();
					this.set_query_parameters();
				}
			}
		},
		methods: {
			set_query_parameters() {
				let search_params = new URLSearchParams(window.location.search);

				search_params.set('paged', this.page);
				search_params.set('per_page', this.per_page);

				if ( this.filter_succeeded !== null && this.filter_succeeded !== "" ) {
					search_params.set('succeeded', this.filter_succeeded);
				} else {
					search_params.delete('succeeded');
				}

				if ( this.filter_type !== null && this.filter_type !== "" ) {
					search_params.set('type', this.filter_type);
				} else {
					search_params.delete('type');
				}

				if ( this.filter_source !== null && this.filter_source !== "" ) {
					search_params.set('source', this.filter_source);
				} else {
					search_params.delete('source');
				}

				if ( this.filter_method !== null && this.filter_method !== "" ) {
					search_params.set('method', this.filter_method);
				} else {
					search_params.delete('method');
				}

				let relative_path_query = window.location.pathname + '?' + search_params.toString();
				history.pushState(null, '', relative_path_query);
			},
			refresh_objects() {
				const url = "/wp-json/wp/v2/suc_synchronized/";
				const search_parameters = {
					page: this.page,
					per_page: this.per_page,
				}

				if (this.filter_succeeded !== null && this.filter_succeeded !== "") {
					search_parameters['succeeded'] = this.filter_succeeded;
				}

				if (this.filter_type !== null && this.filter_type !== "") {
					search_parameters['type'] = this.filter_type;
				}

				if (this.filter_source !== null && this.filter_source !== "") {
					search_parameters['source'] = this.filter_source;
				}

				if (this.filter_method !== null && this.filter_method !== "") {
					search_parameters['method'] = this.filter_method;
				}

				const search_parameters_obj = new URLSearchParams(search_parameters);
				fetch(url + '?' + search_parameters_obj.toString()).then(result => {
					if (result.status < 200 || result.status >= 300) {
						throw result;
					} else {
						return result;
					}
				}).then(async result => {
					const json_data = await result.json();
					const total_objects = result.headers.get('X-WP-Total');
					const total_pages = result.headers.get('X-WP-TotalPages');
					return {
						'data': json_data,
						'pages': total_pages,
						'count': total_objects,
					};
				}).then(data => {
					data.pages = parseInt(data.pages);
					data.count = parseInt(data.count);
					for (let i = 0; i < data.data.length; i++) {
						if (data.data[i].meta.extra_data === null ||
							data.data[i].meta.extra_data instanceof Array ||
							typeof data.data[i].meta.extra_data !== 'object') {
							data.data[i].meta.extra_data = {};
						}
					}
					return data;
				}).then(data => {
					this.synchronized_objects = data.data;
					this.amount_of_pages = data.pages;
					this.amount_of_objects = data.count;
				}).catch(error => {

				});
			},
			update_page(page_number) {
				this.page = page_number;
			},
			retry_synchronization(object_id, object_type) {
				if (!(object_type in this.retries_in_progress)) {
					this.retries_in_progress[object_type] = [];
				}
				this.retries_in_progress[object_type].push(object_id);
				fetch('/wp-json/snelstart-uphance-coupling/v1/synchronized-objects/retry', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						"Accept": 'application/json',
						"X-WP-Nonce": "<?php echo esc_js( $nonce ); ?>",
					},
					body: JSON.stringify({
						'type': object_type,
						'id': object_id,
					})
				}).then(response => {
					if (response.status < 200 || response.status >= 300) {
						throw response;
					} else {
						return response;
					}
				}).then(response => {
					tata.success('', 'Object synchronized successfully.');
				}).catch((error) => {
					show_error_from_api(error);
				}).finally(() => {
					const index = this.retries_in_progress[object_type].indexOf(object_id);
					if (index > -1) {
						this.retries_in_progress[object_type].splice(index, 1);
					}
				});
			}
		}
	}).mount('#synchronized-objects-list');
</script>
