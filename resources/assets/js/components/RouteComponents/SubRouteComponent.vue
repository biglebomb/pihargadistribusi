<template>
    <div class="row">
        <div class="col-12">
            <div class="c-callout c-callout-info">
                <strong class="h4 text-muted" v-c-tooltip="subroute.kode_plant_asal_name">{{index}}.
                    {{subroute.kode_plant_asal}}</strong>
                <i class="fa fa-arrow-right"></i>
                <strong class="h4 text-muted" v-c-tooltip="subroute.kode_plant_tujuan_name">{{subroute.kode_plant_tujuan}}</strong>
                <br>
                <small>(ID:{{subroute.id}})</small>
            </div>
        </div>

        <div class="col-12">
            <loading :active.sync="loading"
                     :can-cancel="false"
                     color="#467fcf"
                     :is-full-page="false"/>
            <CDataTable
                :items="costs"
                :fields="fields"
                sorter
                hover
                :pagination="false"
            >
                <template #no="{item, index}">
                    <td>{{index+1}}</td>
                </template>
                <template #nama_cost="{item}">
                    <td>
                        <div class="row">
                            <div class="col-9">
                                {{item.cost.nama}}
                            </div>
                            <div class="col-3 text-right">
                                <CButton
                                    color="primary"
                                    variant="outline"
                                    square
                                    size="sm"
                                    @click="toggleDetails(item, index)"
                                >
                                    {{Boolean(item._toggled) ? 'Hide Materials' : 'Show Materials'}}
                                </CButton>
                            </div>
                        </div>
                    </td>
                </template>
                <template #cost_per_ton="{item}">
                    <td class="text-right">Rp.{{item.cost_per_ton | currency}}</td>
                </template>
                <template #details="{item}">
                    <CCollapse :show="Boolean(item._toggled)" :duration="collapseDuration">
                        <MaterialSubRouteComponent :routecost_id="subroute.id" :cost_id="item.cost.id"/>
                    </CCollapse>
                </template>
            </CDataTable>
        </div>
        <!--                <table class="table table-striped table-borderless">-->
        <!--                    <loading :active.sync="loading"-->
        <!--                             :can-cancel="false"-->
        <!--                             color="#467fcf"-->
        <!--                             :is-full-page="false"/>-->
        <!--                    <thead>-->
        <!--                    <tr>-->
        <!--                        <th scope="col">#</th>-->
        <!--                        <th class="table-subroute" scope="col">Material</th>-->
        <!--                        <th scope="col">Detil cost</th>-->
        <!--                        <th scope="col">Tanggal</th>-->
        <!--                        <th scope="col" class="text-right">Cost</th>-->
        <!--                    </tr>-->
        <!--                    </thead>-->
        <!--                    <tbody>-->
        <!--                    <tr v-for="(cost, index) in costs">-->
        <!--                        <td scope="row">{{index+1}}</td>-->
        <!--                        <td>{{cost.material.nama}}</td>-->
        <!--                        <td>{{cost.cost.nama}}</td>-->
        <!--                        <td>{{getReadableDate(cost.cost_date)}}</td>-->
        <!--                        <td class="text-right">Rp.{{cost.cost_per_ton | currency}}*</td>-->
        <!--                    </tr>-->
        <!--                    <tr>-->
        <!--                        <th scope="row"></th>-->
        <!--                        <th scope="row" colspan="1">Total</th>-->
        <!--                        <th class="text-right"><strong>Rp.{{total | currency}}</strong></th>-->
        <!--                    </tr>-->
        <!--                    </tbody>-->
        <!--                </table>-->
    </div>
</template>

<style>
    .table-subroute {
        width: 40%;
        overflow: auto;
    }
</style>
<script>
    import Loading from 'vue-loading-overlay';
    import {CButton, CCollapse, CDataTable, CTooltip} from '@coreui/vue';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import MaterialSubRouteComponent from "./MaterialSubRouteComponent";

    export default {
        props: ['subroute', 'index'],

        components: {MaterialSubRouteComponent, Loading, CDataTable, CButton, CCollapse},
        directives: {
            CTooltip
        },

        data: () => ({
            costs: [],
            materials: [],
            total: 0,
            loading: false,
            collapseDuration: 0,
            total_material: 0,

            fields: [
                {key: 'no', _style: 'width:5%'},
                {key: 'nama_cost', _style: 'width:40%;'},
                {key: 'cost_per_ton', _style: 'width:20%;'}
            ]
        }),

        methods: {
            async fetchCost() {
                this.loading = true
                await axios.get(`/routecost/${this.subroute.id}`).then(response => {
                    this.loading = false
                    this.costs = response.data.result
                    this.costs.forEach(item => {
                        this.total += item.cost_per_ton
                    })
                })
                    .catch(error => {
                        this.loading = false
                    })
            },
            getReadableDate(date) {
                let d = new Date(date)
                return d.toLocaleDateString('id-ID')
            },

            async toggleDetails(item, index) {
                this.$set(this.costs.find(i => i.cost_id === item.cost_id), '_toggled', !item._toggled)
                if (item._toggled) {
                    this.loading = true
                    this.collapseDuration = 300
                    this.$nextTick(() => {
                        this.collapseDuration = 0
                        this.loading = false
                    })
                }
            }
        },

        mounted() {
            this.fetchCost()
        },
        filters: {
            currency(value) {
                let val = (value / 1).toFixed(2).replace('.', ',')
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            }
        }
    }
</script>
