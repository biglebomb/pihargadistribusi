<template>
    <div id="app">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="bylini-tab" data-toggle="tab" href="#bylini" role="tab" aria-controls="home" aria-selected="true">Berdasarkan Lini</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="byregency-tab" data-toggle="tab" href="#byregency" role="tab" aria-controls="profile" aria-selected="false">Berdasarkan Kabupaten</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="bylini" role="tabpanel" aria-labelledby="bylini-tab">
                <find-route-component/>
            </div>
            <div class="tab-pane fade" id="byregency" role="tabpanel" aria-labelledby="byregency-tab">
                <find-lini-by-kabupaten/>
            </div>
        </div>
    </div>
</template>
<script>
    import FindRouteComponent from "./ByLini/FindRouteComponent";
    import FindLiniByKabupaten from "./ByRegency/FindLiniByKabupaten";
    export default {
        components: {FindLiniByKabupaten, FindRouteComponent},
    }
</script>
