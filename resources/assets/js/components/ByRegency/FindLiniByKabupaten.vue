<template>
    <div class="card">
        <div class="card-body">
            <loading :active.sync="loading"
                     :can-cancel="false"
                     color="#467fcf"
                     :is-full-page="false"/>
            <h5 class="card-title">Cek harga rute</h5>
            <h6 class="card-subtitle mb-2 text-muted">Pencarian rute berdasarkan lini</h6>
            <div class="row">
                <div class="col-sm-12 col-12">
                    <select v-model="selectedProvince" class="custom-select custom-select-lg mb-3" @change="provinceChange">
                        <option value="0" disabled selected>Pilih Provinsi</option>
                        <option v-for="item in provinces" :value="item.id">{{item.name}}
                        </option>
                    </select>
                </div>
                <div class="col-sm-12 col-12">
                    <select v-model="selectedRegency" class="custom-select custom-select-lg mb-3">
                        <option value="0" disabled selected>Pilih Kabupaten</option>
                        <option v-for="item in regencies" :value="item.id">{{item.name}}
                        </option>
                    </select>
                </div>
                <div class="col-sm-12 col-12">
                    <select v-model="selectedGroup" class="custom-select custom-select-lg mb-3">
                        <option value="0" disabled selected>Pilih Grup Material</option>
                        <option value="all" selected>Semua</option>
                        <option v-for="item in materialgroup" :value="item.nama">{{item.nama}}
                        </option>
                    </select>
                </div>
            </div>
            <button @click="findRoute" :disabled="selectedRegency === 0 && selectedGroup === 0" class="btn btn-primary mb-3">Cari</button>
            <button @click="reset" class="btn btn-secondary mb-3">Reset</button>
            <div v-if="errorMessage !== null" class="alert alert-danger" role="alert">
                {{errorMessage}}
            </div>
            <div class="row" v-if="routesShown">
                <div class="col-12">
                    <CDataTable
                        :items="routes"
                        :fields="fields"
                        items-per-page-select
                        :items-per-page="10"
                        sorter
                        hover
                        pagination
                    >
                        <template #no="{item, index}">
                            <td>{{index+1}}</td>
                        </template>
                        <template #route="{item}">
                            <td>
                                <template v-for="(i, index) in item.route">
                                    <span v-c-tooltip="i.name">{{i.kode + (index !== item.route.length-1 ? ' >' : '')}}</span>
                                </template>
                            </td>
                        </template>
                        <template #show_details="{item, index}">
                            <td class="py-2">
                                <CButton
                                    color="primary"
                                    variant="outline"
                                    square
                                    size="sm"
                                    @click="toggleDetails(item, index)"
                                >
                                    {{Boolean(item._toggled) ? 'Hide' : 'Show'}}
                                </CButton>
                            </td>
                        </template>
                        <template #total_cost="{item}">
                            <td>Rp.{{item.total_cost | currency}}</td>
                        </template>
                        <template #details="{item}">
                            <CCollapse :show="Boolean(item._toggled)" :duration="collapseDuration">
                                <route-component :route="item"/>
                            </CCollapse>
                        </template>
                    </CDataTable>
<!--                    <table class="table table-striped table-borderless">-->
<!--                        <loading :active.sync="loading"-->
<!--                                 :can-cancel="false"-->
<!--                                 color="#467fcf"-->
<!--                                 :is-full-page="false"/>-->
<!--                        <thead>-->
<!--                        <tr>-->
<!--                            <th scope="col">#</th>-->
<!--                            <th>Supply Plant</th>-->
<!--                            <th class="table-subroute" scope="col">Rute</th>-->
<!--                            <th scope="col">Estimasi cost</th>-->
<!--                            <th>Detil rute</th>-->
<!--                        </tr>-->
<!--                        </thead>-->
<!--                        <tbody>-->
<!--                        <tr v-for="(route, index) in routes">-->
<!--                            <td scope="row">{{index+1}}</td>-->
<!--                            <td>{{route.supply_plant}}</td>-->
<!--                            <td>-->
<!--                                <template v-for="(item, index) in route.route">-->
<!--                                    {{item.kode + (index !== route.route.length-1 ? '>' : '')}}-->
<!--                                </template>-->
<!--                            </td>-->
<!--                            <td>-->
<!--                                Rp.{{route.total_cost | currency}}-->
<!--                            </td>-->
<!--                            <td>-->
<!--                                <button class="btn btn-primary" @click="checkRouteDetail(index)">Detil</button>-->
<!--                            </td>-->
<!--                        </tr>-->
<!--                        </tbody>-->
<!--                    </table>-->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import RouteComponent from "../RouteComponents/RouteComponent";
    // Import component
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    import {CDataTable, CTooltip, CButton, CCollapse} from '@coreui/vue';

    export default {
        components: {RouteComponent, Loading, CDataTable, CButton, CCollapse},
        directives: {
            'c-tooltip': CTooltip
        },
        data: () => ({
            provinces: [],
            regencies: [],
            linis: [],
            materialgroup: [],
            selectedProvince: 0,
            selectedRegency: 0,
            selectedGroup: 0,
            routes: null,
            routesShown: false,
            loading: false,
            errorMessage: null,
            collapseDuration: 0,

            fields: [
                {key: 'no', _style: 'width:5%'},
                {key: 'supply_plant', _style: 'width:20%'},
                {key: 'route', _style: 'width:40%;'},
                {key: 'total_cost', _style: 'width:20%;'},
                {
                    key: 'show_details',
                    label: '',
                    _style: 'width:1%',
                    sorter: false,
                    filter: false
                }
            ]
        }),
        computed: {},
        methods: {
            async provinceChange() {
                this.loading = true;
                await this.fetchRegencies(this.selectedProvince)
                this.loading = false;
                this.linis = []
            },
            async regencyChange(){
                this.loading = true;
                await this.fetchLinis(this.selectedRegency)
                this.loading = false
            },
            checkRouteDetail(index){
                this.selectedRoute = null
                setTimeout(() => {
                    this.selectedRoute = this.routes[index]
                }, 100)
                let el = this.$el.getElementsByClassName("route-detail")[0];
                el.scrollIntoView({ behavior: 'smooth' })
            },
            async fetchProvinces() {
                this.loading = true;
                await axios.get('/provinces').then(response => {
                    this.provinces = response.data
                })
                this.loading = false
            },
            async fetchRegencies(province_id){
                await axios.get(`/province/${province_id}/regency`).then(response => {
                    this.regencies = response.data
                })
            },
            async fetchLinis(regency_id){
                await axios.get(`/regency/${regency_id}`).then(response => {
                    this.linis = response.data.linis
                })
            },
            async fetchMaterialGroup(){
                await axios.get(`/materialgroup`).then(response => {
                    this.materialgroup = response.data
                })
            },

            async findRoute() {
                this.selectedRoute = null
                this.routesShown = false
                this.loading = true
                if (this.routeFrom !== 'default' && this.routeTo !== 'default') {
                    await axios.get(`/findroutebyregency/${this.selectedRegency}/${this.selectedGroup}`)
                        .then(response => {
                            if(response.data.success){
                                this.routes = response.data.result
                                this.routesShown = true
                                this.errorMessage = null
                            } else {
                                this.errorMessage = response.data.message
                                this.routes = null
                                this.routesShown = false
                            }
                            this.loading = false
                        })
                        .catch(error => {
                            this.errorMessage = "Terjadi kesalahan dari server."
                            this.loading = false
                        })
                }
            },

            reset() {
                this.routes = null
                this.routesShown = false
                this.selectedRoute = null
            },

            toggleDetails(item, index) {
                this.$set(this.routes.find(i => i.no === item.no), '_toggled', !item._toggled)
                this.collapseDuration = 300
                this.$nextTick(() => {
                    this.collapseDuration = 0
                })
            }

        },
        filters: {
            currency(value) {
                let val = (value / 1).toFixed(2).replace('.', ',')
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            }
        },
        mounted() {
            this.fetchProvinces()
            this.fetchMaterialGroup()
        }
    }
</script>
