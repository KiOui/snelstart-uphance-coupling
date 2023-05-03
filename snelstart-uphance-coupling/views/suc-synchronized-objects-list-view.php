<?php

$paged = intval( $_GET['paged'] );
if ( $paged <= 0 ) {
	$paged = 1;
}

$per_page = intval( $_GET['per_page'] );
if ( $per_page <= 0 ) {
	$per_page = 20;
}

?>

<div class="suc-synchronized-objects-list wrap">
	<h1 class="wp-heading-inline mb-2">Synchronized Uphance objects</h1>
	<div id="synchronized-objects-list">
		<div class="mb-2">
			<input type="text" class="ml-auto" id="search-input"/>
		</div>
		<table class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<th>Object ID</th>
					<th>Object type</th>
					<th>Date</th>
					<th>Object URL</th>
					<th>Succeeded</th>
					<th>Details</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(object, index) of synchronized_objects" :key="`synchronized_object_${object.id}`">
					<td>
						{{ object.meta.id }}
					</td>
					<td>
						{{ object.meta.type }}
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
			<tfoot>
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
							<option value="500">500</option>
							<option value="1000">1000</option>
						</select>
						<select class="pagenum input-mini" title="Select page number" v-model="page">
							<template v-for="index in amount_of_pages" :key="index">
								<option :value="index">{{ index }}</option>
							</template>
						</select>
					</th>
					<th colspan="4"></th>
				</tr>
			</tfoot>
		</table>
		<div v-for="(object, index) of synchronized_objects" :key="`synchronized_object_${object.id}`" class="modal fade" :id="`details-modal-${index}`" tabindex="-1" role="dialog" :aria-labelledby="`details-modal-${index}-label`" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">#{{ object.meta.id }}</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<table class="wp-list-table widefat fixed striped table-view-list posts">
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
								<template v-if="object.meta.extra_data.length > 0">
									<tr v-for="(key, value) of Object.entries(object.meta.extra_data)">
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
							<h2>Error message</h2>
							<p>
								{{ object.meta.error_message }}
							</p>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-danger">Retry Synchronization</button>
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
				synchronized_objects: [],
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
                }
			},
			per_page: {
                handler(val, oldVal) {
                    if (this.per_page <= 0) {
                        this.per_page = 20;
                    }
                    this.page = 1;
                    this.refresh_objects();
                }
			}
		},
		methods: {
			refresh_objects() {
				const url = "/wp-json/wp/v2/suc_synchronized/";
				const search_parameters = {
					page: this.page,
					per_page: this.per_page,
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
			}
		}
	}).mount('#synchronized-objects-list');
</script>
