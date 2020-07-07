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
                <div class="col-sm-5 col-12">
                    <select v-model="routeFrom" class="custom-select custom-select-lg mb-3" @change="routeChange">
                        <option value="default" disabled selected>Pilih gudang asal...</option>
                        <option v-for="item in liniFrom" :value="item.kode_plant">{{item.kode_plant}} -
                            {{item.nama_plant}}
                        </option>
                    </select>
                </div>
                <div class="col-sm-2 col-12 text-center align-content-center justify-content-center">
                    <i class="fa fa-arrow-right" style="font-size: 45px"/>
                </div>
                <div class="col-sm-5 col-12">
                    <select v-model="routeTo" class="custom-select custom-select-lg mb-3" @change="routeChange">
                        <option value="default" disabled selected>Pilih gudang tujuan...</option>
                        <option v-for="item in liniToFiltered" :value="item.kode_plant">{{item.kode_plant}} -
                            {{item.nama_plant}}
                        </option>
                    </select>
                </div>
            </div>
            <button @click="findRoute" :disabled="routeFrom === 'default' || routeTo === 'default'" class="btn btn-primary mb-3">Cari</button>
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
            routeFrom: 'default',
            routeTo: 'default',
            liniTo: null,
            liniToFiltered: null,
            liniFrom: null,
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
            routeChange() {
                this.liniToFiltered = this.liniTo.filter(item =>
                    item.kode_plant.charAt(0) === this.routeFrom.charAt(0)
                    &&
                    item.lini >= this.liniFrom.find(find => find.kode_plant === this.routeFrom).lini)
            },
            async findRoute() {
                this.selectedRoute = null
                this.routesShown = false
                this.loading = true
                if (this.routeFrom !== 'default' && this.routeTo !== 'default') {
                    await axios.get(`/findroute/${this.routeFrom}/${this.routeTo}`)
                        .then(response => {
                            if (response.data.success) {
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
            async fetchLinis() {
                await axios.get('/linis').then(response => {
                    this.liniTo = response.data
                    this.liniToFiltered = this.liniTo
                    this.liniFrom = response.data.filter(item => item.lini !== 3)
                })
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
            this.fetchLinis()
        }
    }
</script>
