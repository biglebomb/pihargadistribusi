<template>
<!--    <div class="col-12 route-detail">-->
<!--        <div class="c-callout c-callout-info">-->
            <div class="card border-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-8">
                            <template v-for="(item, index) in route.route">
                                <div>
                                    <strong class="h3">{{item.kode}}</strong>
                                    <small>({{item.name}})</small>
                                </div>
                                <i v-if="index !== route.route.length-1" class="fa fa-arrow-down"/>
                            </template>
                        </div>
                        <div class="col-4 text-right">
                            <small>Estimasi cost</small>
                            <br>
                            <strong class="h3">
                                Rp.{{route.total_cost | currency}}
                            </strong>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-title">Daftar sub-rute</div>
                    <sub-route-component v-if="subrouteShown" v-for="(item, index) in route.subroute" :subroute="item" :index="index+1" :key="index"/>
                    <button @click="showSubRoute" class="btn btn-primary">{{subrouteText}}</button>
                </div>
                <div class="card-footer">
                    <small>* Nilai yang ditampilkan adalah hasil rata - rata dari harga per ton beberapa material yang dikelompokan dengan jenis costnya.</small>
                </div>
            </div>
<!--        </div>-->
<!--    </div>-->
</template>
<script>
    import SubRouteComponent from "./SubRouteComponent";
    export default {
        components: {SubRouteComponent},
        props:['route', 'index'],
        data: () => ({
            subrouteShown: false,
            subrouteText: "Tampilkan sub-rute",
        }),

        methods: {
            async showSubRoute(){
                if(this.subrouteShown) {
                    this.subrouteText = "Tampilkan sub-rute"
                    this.subrouteShown = false
                } else {
                    this.subrouteText = "Sembunyikan sub-rute"
                    this.subrouteShown = true
                }
            },
            calculateTotalCost(){

            }
        },

        mounted(){
            // this.$root.$on('routeChanges', function(){
            //     console.log("Received")
            //     this.subrouteText = "Tampilkan sub-rute"
            //     this.subrouteShown = false
            // })
        },
        filters: {
            currency(value) {
                let val = (value / 1).toFixed(2).replace('.', ',')
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            }
        }
    }
</script>
