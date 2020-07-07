<template>
    <div class="col-12">
        <div class="card card-body">
            <table class="table table-striped table-borderless">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th class="table-subroute" scope="col">Material</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col" class="text-right">Cost</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(cost, index) in materials">
                    <td scope="row">{{index+1}}</td>
                    <td>{{cost.material.nama}}</td>
                    <td>{{getReadableDate(cost.cost_date)}}</td>
                    <td class="text-right">Rp.{{cost.cost_per_ton | currency}}</td>
                </tr>
                </tbody>
            </table>
        </div>
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
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        props: ['routecost_id', 'cost_id'],

        components: {},
        directives: {
        },

        data: () => ({
            materials: [],
            total: 0,
            loading: false,
        }),

        methods: {
            async fetchMaterial() {
                this.loading = true
                await axios.get(`/routecost/${this.routecost_id}/${this.cost_id}`).then(response => {
                    this.loading = false
                    this.materials = response.data.result
                    this.materials.forEach(item => {
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
        },

        mounted() {
            this.fetchMaterial()
        },
        filters: {
            currency(value) {
                let val = (value / 1).toFixed(2).replace('.', ',')
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            }
        }
    }
</script>
