<template>
    <van-action-sheet v-model="visible"
                      title="抽奖记录"
                      :overlay="true"
                      :style="{ height:'100%' }"
                      duration="0.3">
        <van-form @submit="onSubmit">
            <van-field
                readonly
                clickable
                name="datetimePicker"
                :value="form.start_at"
                label="开始时间"
                placeholder="点击选择时间"
                @click="showPickerByTag('start_at')"
            />
            <van-field
                readonly
                clickable
                name="datetimePicker"
                :value="form.end_at"
                label="结束时间"
                placeholder="点击选择时间"
                @click="showPickerByTag('end_at')"
            />
            <div style="margin: 16px;">
                <van-button v-if="!record_loading" round block type="info" native-type="submit" >
                    查询
                </van-button>
                <van-button v-else loading round block type="info" loading-text="查询中..." />
            </div>
        </van-form>

        <van-empty v-if="record_list.length==0" description="没有记录哦" />
        <div class="record_item" v-else v-for="(item,key) in record_list" >
            <van-row>
                <van-col span="14">时间：<em>{{ item.created_at }}</em></van-col>
                <van-col span="10">奖期：<em>{{item.activity_issue_id}}</em></van-col>
            </van-row>
            <van-row>
                <van-col span="8">号码：<em>{{ item.code }}</em></van-col>
                <van-col span="8">开奖号码：<em v-if="item.open_code!=null">{{ item.open_code }}</em></van-col>
                <van-col span="8">
                    状态：<van-tag v-if="item.open_code==null" type="warning">未开奖</van-tag>
                    <van-tag v-else-if="item.draw_level==0" type="success">一等奖</van-tag>
                    <van-tag v-else-if="item.draw_level==1" type="success">二等奖</van-tag>
                    <van-tag v-else-if="item.draw_level==2" type="success">三等奖</van-tag>
                    <van-tag v-else-if="item.draw_level==3" type="success">四等奖</van-tag>
                    <van-tag v-else-if="item.draw_level==4" type="success">五等奖</van-tag>
                    <van-tag v-else >未中奖</van-tag>
                </van-col>
            </van-row>
        </div>

        <van-pagination
            v-model="form.page"
            :total-items="totalPage"
            :items-per-page="5"
            @change="changePage"
        />

        <van-popup v-model="showPicker" position="bottom">
            <van-datetime-picker
                type="date"
                v-model="time"
                @confirm="onConfirm"
                @cancel="showPicker = false"
            />
        </van-popup>
    </van-action-sheet>
</template>

<script>
    import { getRecord } from '@/api/activity'
    import { ActionSheet,DatetimePicker,Empty,Pagination,Col,Row,Tag } from 'vant'

    export default {
        name: "activity_record",
        computed: {
        },
        components: {
            [DatetimePicker.name]: DatetimePicker,
            [ActionSheet.name]: ActionSheet,
            [Empty.name]: Empty,
            [Pagination.name]: Pagination,
            [Col.name]: Col,
            [Row.name]: Row,
            [Tag.name]: Tag,
        },
        data(){
            return {
                visible: false,
                form:{
                    ident:'raffle_tickets',
                    start_at: '',
                    end_at: '',
                    page:0,
                },
                totalPage:0,
                totalRow:0,
                showPicker:false,
                time:new Date(),
                picker_tag:'',
                record_loading:false,
                record_list:[],
            };
        },
        created() {
            this.$eventBus.$on('ui_toggleRightMenu',(val)=>{
                this.visible = val

                if( this.visible ){
                    this.onSubmit();
                }
            });
        },
        methods:{
            onSubmit(values) {
                console.log('submit', values);
                this.record_loading = true;
                getRecord( this.form ).then( response => {
                    this.record_loading = false;
                    if( response.data.code == 1 ){
                        this.totalRow = response.data.data.total;
                        this.totalPage = Math.floor(this.totalRow/20);
                        this.record_list = response.data.data.activity_record;
                    }else{
                        this.$notify({ type: 'danger', message: response.data.data.msg ,duration:1000});
                    }
                });
            },
            changePage(){
                this.onSubmit();
            },
            onConfirm(time) {
                let month = (time.getMonth()+1 + '').padStart(2,'0');
                let day = (time.getDate() + '').padStart(2,'0');
                let time_format = time.getFullYear() + '-' + month + '-' + day;

                if( this.picker_tag == 'start_at' ){
                    this.form.start_at = time_format
                }else{
                    // 检查时间
                    let start_at = new Date(this.form.start_at);
                    if( start_at >= time ){
                        this.$notify({ type: 'danger', message: '结束时间不能大于开始时间！' ,duration:1000});
                        return;
                    }

                    this.form.end_at = time_format;
                }
                this.showPicker = false;
            },
            showPickerByTag(tag){
                this.picker_tag = tag;
                this.showPicker = true
            },
            // getStatus( item ){
            //     if( item. )
            //     return '未开奖';
            // }
        }
    }
</script>

<style lang="scss" scoped>
    .van-action-sheet{
        max-height: 90%;
    }
    .record_item{
        background-color: #fff4e7;
        border-radius: 5px;
        margin: 10px 5px;
        padding: 9px;
        font-size: 13px;
        line-height: 20px;
        color:#636363;

        em{
            font-style: normal;
            color:#ff8049;
        }
    }
</style>
