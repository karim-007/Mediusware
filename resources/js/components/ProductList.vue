<template>
    <div>
        <form action="javascript:void(0)" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" v-model="name" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <b-dropdown id="dropdown-1" v-model="variant" variant="light"  text="Variant" class="m-md-2">
                        <b-dropdown-item value="null">Select One</b-dropdown-item>
                        <b-dropdown-item>Second Action</b-dropdown-item>
                        <b-dropdown-item>Third Action</b-dropdown-item>
                        <b-dropdown-divider>ddd</b-dropdown-divider>
                        <b-dropdown-item active>Active action</b-dropdown-item>
                        <b-dropdown-item disabled>Disabled action</b-dropdown-item>
                    </b-dropdown>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" v-model.number="p_from" name="price_from" aria-label="First name" placeholder="From" class="form-control">
                        <input type="text" v-model.number="p_to" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" v-model="f_date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <!-- table -->
        <vue-good-table
            :columns="columns"
            :rows="filter_products"
            :search-options="{
        enabled: true,
        externalQuery: searchTerm }"
            :pagination-options="{
        enabled: true,
        perPage:pageLength
      }"
        >
            <template
                slot="table-row"
                slot-scope="props"
            >
                <!-- Column: index -->
                <span v-if="props.column.field === 'id'">
                        {{ props.row.originalIndex + 1 }}
                </span>
                        <!-- Column: Status -->
                <span v-else-if="props.column.field === 'variant'">
                    <template>
                        <b-row v-for="(vv,key) in props.row.product_variant_prices" :key="key">
                            <b-col md="4">
                                {{vv.product_variant_one?vv.product_variant_one.variant:''}}
                                {{vv.product_variant_two?'/'+vv.product_variant_two.variant:''}}
                                {{vv.product_variant_three?'/'+vv.product_variant_three.variant:''}}
                            </b-col>
                            <b-col md="4">Price : {{ parseFloat(vv.price).toFixed(2) }}</b-col>
                            <b-col md="4">InStock : {{ parseFloat(vv.stock).toFixed(2)  }}</b-col>
                        </b-row>
                        <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>

                    </template>

                    </span>
                <span v-else-if="props.column.field === 'created_at'">
                    {{ moment(props.row.created_at).format('DD-MM-Y') }}
                </span>
                <!-- Column: Action -->
                <span v-else-if="props.column.field === 'action'">
                    <template>
<!--                      <b-button variant="danger">
                      </b-button>-->
                        <div class="btn-group btn-group-sm">
                            <a :href="`/product/${props.row.id}/edit`" class="btn btn-success">Edit</a>
                        </div>
                    </template>
                </span>
            </template>

            <!-- pagination -->
            <template
                slot="pagination-bottom"
                slot-scope="props"
            >
                <div class="d-flex justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-0 mt-1">
            <span class="text-nowrap ">
              Showing 1 to
            </span>
                        <b-form-select
                            v-model="pageLength"
                            :options="['5','10','15']"
                            class="mx-1"
                            @input="(value)=>props.perPageChanged({currentPerPage:value})"
                        />
                        <span class="text-nowrap"> of {{ props.total }} entries </span>
                    </div>
                    <div>
                        <b-pagination
                            :value="1"
                            :total-rows="props.total"
                            :per-page="pageLength"
                            first-number
                            last-number
                            align="right"
                            prev-class="prev-item"
                            next-class="next-item"
                            class="mt-1 mb-0"
                            @input="(value)=>props.pageChanged({currentPage:value})"
                        >
                            <template #prev-text>
                                &#60;
                            </template>
                            <template #next-text>
                                &#62;
                            </template>
                        </b-pagination>
                    </div>
                </div>
            </template>
        </vue-good-table>
    </div>
</template>
<script>
import {
    BButton,BBadge, BPagination, BFormGroup, BFormInput, BFormSelect,
    BModal, BForm,BRow, BCol,BFormCheckbox,BDropdown,BDropdownItem,BDropdownDivider
} from 'bootstrap-vue'
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.css'
import moment from "moment"
export default {
    name: 'ProductList',
    components: {
        VueGoodTable,
        BBadge,BFormCheckbox,
        BPagination,
        BFormGroup,
        BFormInput,
        BFormSelect,
        BButton,
        BModal,
        BForm,BRow, BCol,BDropdown,BDropdownItem,BDropdownDivider
    },
    data() {
        return {
            moment,
            products:[],
            filter_products:[],
            name: '',
            variant:null,
            p_from:null,
            p_to:null,
            f_date:null,
            pageLength: 10,
            dir: false,
            model_mode:'add',
            selected_row:{},
            columns: [
                {
                    label: '#',
                    field: 'id',
                },
                {
                    label: 'Title',
                    field: 'title',
                },
                {
                    label: 'Description',
                    field: 'description',
                },
                {
                    label: 'Variant',
                    field: 'variant',
                },
                {
                    label: 'Created',
                    field: 'created_at',
                },
                {
                    label: 'Action',
                    field: 'action',
                },
            ],
            searchTerm: '',
        }
    },
    created() {
        this.getProducts();
    },
    methods:{
        async getProducts(){
            await axios.get('/get/product/list').then((response)=>{
                this.products=response.data;
            }).catch(()=>{
                this.products=[];
            });
        },
        filterProduct(){
          this.filter_products=this.products;
            if (this.name !== ''){
                this.filter_products = this.products.filter(item=> {
                    return item.title.toLowerCase().indexOf(this.name.toLowerCase()) > -1
                });
            }
            if (this.f_date !== null){
                this.filter_products = this.filter_products.filter(item=> {
                    if(moment(item.created_at).format('YMMDD') === moment(this.f_date).format('YMMDD')){
                        return item;
                    }
                });
            }
            if (this.p_from !== null){
                this.filter_products = this.filter_products.map(item=> {
                    let min = Math.min(...item.product_variant_prices.map(d=>d.price));
                    let max = Math.max(...item.product_variant_prices.map(d=>d.price));
                    if (max>=this.p_from || min<=this.p_from) return item;
                }).filter(Boolean);
            }
            if (this.p_to !== null){
                this.filter_products = this.filter_products.map(item=> {
                    //let min = Math.min(...item.product_variant_prices.map(d=>d.price));
                    let max = Math.max(...item.product_variant_prices.map(d=>d.price));
                    if (max <= this.p_from) return item;
                }).filter(Boolean);
            }
        },
    },
    watch:{
        products(){
            this.filterProduct();
        },
        name(){
            this.filterProduct();
        },
        variant(){
            this.filterProduct();
        },
        p_from(){
            this.filterProduct();
        },
        p_to(){
            this.filterProduct();
        },
        f_date(){
            this.filterProduct();
        },
    }
}
</script>
