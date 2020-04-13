<template>
        <div class="app-container" v-loading="loading">

            <sticky :z-index="10" :class-name="'sub-navbar '+status">
                <el-button v-loading="loading" style="margin-left: 10px;" type="success" @click="submitForm">
                    保存
                </el-button>
            </sticky>

            <div class="container">
                <el-form ref="postForm" :model="postForm" :rules="rules" class="form-container">
                    <div class="createPost-main-container">
                        <el-form-item label-width="120px" v-if="this.isEdit" label="活动标题:" class="postInfo-container-item" >
                            <el-input
                                placeholder="请输入标题"
                                :disabled="true"
                                v-model="title">
                            </el-input>
                        </el-form-item>
                        <el-form-item label-width="120px" label="活动时间:" class="postInfo-container-item">
                            <el-date-picker
                                v-model="postForm.activity_at"
                                type="datetimerange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item label-width="120px" label="奖券总量（千）:" class="postInfo-container-item">
                            <el-input-number v-model="postForm.extra.tickets_total" :min="1" :max="1000" label="千为单位"></el-input-number>
                        </el-form-item>
                        <el-form-item label-width="120px" label="奖品:" class="postInfo-container-item">
                            <el-table
                                :data="postForm.extra.prize_level"
                                stripe
                                style="width: 100%">
                                <el-table-column
                                    label="奖级"
                                    width="180">
                                    <template slot-scope="scope">
                                        <el-input
                                            placeholder="奖级名称"
                                            v-model="scope.row.name">
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    label="奖品名"
                                    width="180">
                                    <template slot-scope="scope">
                                        <el-input
                                            v-model="scope.row.prize_name">
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    label="奖品图片">
                                    <template slot-scope="scope">
                                        <img v-if="scope.row.prize_img" :src="imagePath+scope.row.prize_img" class="prize_img">
                                        <el-select v-model="scope.row.prize_img" placeholder="请选择">
                                            <el-option
                                                v-for="(item,key) in fileList"
                                                :key="key"
                                                :label="item.name"
                                                :value="item.name">
                                            </el-option>
                                        </el-select>
                                        <el-upload
                                            class="upload-demo"
                                            action="/upload"
                                            :on-preview="handlePreview"
                                            :on-remove="handleRemove"
                                            :on-success="handleUploadSuccess"
                                            :before-upload="beforeAvatarUpload"
                                            :data="imagePostData"
                                            :show-file-list="false"
                                            :file-list="fileList"
                                            list-type="picture">
                                            <el-button size="small" type="primary" @click="startUpload(scope)">点击上传<i class="el-icon-upload el-icon--right"></i></el-button>
                                        </el-upload>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    fixed="right"
                                    label="操作"
                                    width="150">
                                    <template slot-scope="scope">
                                        <el-button
                                            size="mini"
                                            type="text"
                                            @click="handleTableDelete(scope.$index, 'prize_level')">删除</el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-button type="primary" size="small" @click="addRow('prize_level')">增加<i class="el-icon-circle-plus el-icon--right"></i></el-button>
                        </el-form-item>
                        <el-form-item label-width="120px" label="号码分配规则:" class="postInfo-container-item">
                            <el-table
                                :data="postForm.extra.tickets_rule"
                                stripe
                                style="width: 100%">
                                <el-table-column
                                    type="index"
                                    width="100">
                                </el-table-column>
                                <el-table-column
                                    label="范围">
                                    <template slot-scope="scope">
                                        <el-row>
                                            <el-col :span="8">
                                                <el-form-item label-width="100px" label="开始号码:" class="postInfo-container-item">
                                                    <el-input
                                                        placeholder="开始号码"
                                                        v-model="scope.row.range[0]">
                                                    </el-input>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="8">
                                                <el-form-item label-width="100px" label="结束号码:" class="postInfo-container-item">
                                                    <el-input
                                                        placeholder="结束号码"
                                                        v-model="scope.row.range[1]">
                                                    </el-input>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    fixed="right"
                                    label="操作"
                                    width="150">
                                    <template slot-scope="scope">
                                        <el-button
                                            size="mini"
                                            type="text"
                                            @click="handleTableDelete(scope.$index, 'tickets_rule')">删除</el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-button type="primary" size="small" @click="addRow('tickets_rule')">增加<i class="el-icon-circle-plus el-icon--right"></i></el-button>
                            <el-button type="warning" size="small" @click="reNewTicketsRule">重新生成规则<i class="el-icon-warning el-icon--right"></i></el-button>
                        </el-form-item>
                    </div>
                </el-form>
            </div>
        </div>
</template>

<script>
    import Tinymce from '@/components/Tinymce'
    import Sticky from '@/components/Sticky' // 粘性header组件
    import { postCreate, getEdit, putEdit } from '@/api/activity_issue'
    import { deleteDeleteFile } from '@/api/upload'

    export default {
        name: "activity_issue_form",
        components: { Tinymce, Sticky },
        props: {
            isEdit: {
                type: Boolean,
                default: false
            }
        },
        data(){
            let id = this.$route.params && this.$route.params.id;

            let now = new Date();
            let nowTime = now.getTime() ;
            let day = now.getDay();
            let one_day = 24*60*60*1000;

            return {
                loading:false,
                title:'',
                postForm: {
                    id:'',
                    activity_at:[new Date(nowTime - (day-1)*one_day),new Date(nowTime + (7-day)*one_day)],
                    extra: {
                        tickets_total: 100,
                        prize_level: [],
                        tickets_rule: [],
                    }
                },
                rules: {

                },
                status:'draft',
                tempRoute: {},
                imagePostData:{
                    '_token': document.head.querySelector('meta[name="csrf-token"]').content,
                    'path': 'activity/'+id,
                },
                imagePath:'',
                fileList:[],
                id:id,
                last_upload_index:0,
            };
        },
        computed: {
        },
        created() {
            if (this.isEdit) {
                this.fetchData(this.id)
            }

            // Why need to make a copy of this.$route here?
            // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
            // https://github.com/PanJiaChen/vue-element-admin/issues/1221
            this.tempRoute = Object.assign({}, this.$route)
        },
        methods:{
            fetchData(id) {
                if( typeof id == 'undefined' || id == null ) id = 1;
                this.loading = true;
                getEdit( id ).then( response => {
                    this.title = response.data.data.title

                    this.imagePath = response.data.data.file_path

                    delete response.data.data.title
                    delete response.data.data.file_path

                    this.postForm = response.data.data

                    this.postForm.activity_at = [
                        new Date(this.postForm.start_at),
                        new Date(this.postForm.end_at)
                    ];

                    this.fileList = response.data.data.file_list;

                    // set tagsview title
                    this.setTagsViewTitle()

                    // set page title
                    //this.setPageTitle()

                    this.loading = false;
                }).catch(err => {
                    console.log(err)
                    this.loading = false;
                });
            },
            setTagsViewTitle() {
                const title = '编辑活动奖期'
                const route = Object.assign({}, this.tempRoute, { title: `${title}-${this.title}` })
                this.$store.dispatch('tagsView/updateVisitedView', route)
            },
            setPageTitle() {
                const title = '编辑活动奖期'
                document.title = `${title} - ${this.postForm.id}`
            },
            submitForm(){
                this.loading = true;
                if( this.isEdit ){
                    putEdit( this.postForm ).then( response => {
                        let type = 'error';
                        if( response.data.code == 1 ){
                            type = 'success';
                        }

                        this.$message({
                            message: response.data.msg,
                            type
                        });
                        this.loading = false;
                    }).catch( err => {
                        console.log( err );
                        this.loading = false;
                    })
                }else{
                    postCreate( this.postForm ).then( response => {
                        let type = 'error';
                        if( response.data.code == 1 ){
                            type = 'success';
                        }

                        this.$message({
                            message: response.data.msg,
                            type
                        });
                        this.loading = false;
                    }).catch( err => {
                        console.log( err );
                        this.loading = false;
                    })
                }
            },
            // 删除指定数据
            handleTableDelete( index , oeject_name ) {
                let object;
                switch (oeject_name) {
                    case 'tickets_rule':
                        object = this.postForm.extra.tickets_rule;
                        break;
                    case 'prize_level':
                        object = this.postForm.extra.prize_level;
                        break;
                }

                if( typeof object == 'undefined' || object == null ){
                    return;
                }

                object.splice(index, 1);
            },
            addRow( oeject_name ){
                switch (oeject_name) {
                    case 'tickets_rule':
                        this.postForm.extra.tickets_rule.push({'range':[0,0]});
                        break;
                    case 'prize_level':
                        this.postForm.extra.prize_level.push({"name":"","prize_name":"","prize_img":""});
                        break;
                }
            },
            // 重新生成号码规则
            reNewTicketsRule(){
                // 获取活动周期
                let day = parseInt(Math.abs((this.postForm.activity_at[1] - this.postForm.activity_at[0]))/(1000*60*60*24));
                let middle_value = parseInt(Math.floor(this.postForm.extra.tickets_total / day));

                // 重置数据
                this.postForm.extra.tickets_rule = [];

                //获取位数
                let _bit = ((this.postForm.extra.tickets_total-1)+'').length;

                let first_key = 0;
                for(let i=0;i<day;i++){
                    let last_value = 0;
                    if( i == day-1 ){
                        last_value = this.postForm.extra.tickets_total-1;
                    }else{
                        // 特殊规则
                        if( this.postForm.extra.tickets_total == 100 && day == 7 ){
                            if( i==0 || i==1 || i==5 ){
                                last_value = (Array(_bit).join(0) + (first_key + 20)).slice(-_bit)
                            }else{
                                last_value = (Array(_bit).join(0) + (first_key + 10)).slice(-_bit)
                            }
                        }else{
                            last_value = (Array(_bit).join(0) + (first_key + middle_value)).slice(-_bit)
                        }
                    }
                    this.postForm.extra.tickets_rule.push({
                        'range':[
                            (Array(_bit).join(0) + (first_key!=0?first_key+1:first_key)).slice(-_bit),
                            last_value
                        ]
                    });
                    // 特殊规则
                    if( this.postForm.extra.tickets_total == 100 && day == 7 ){
                        if( i==0 || i==1 || i==5 ){
                            first_key += 20;
                        }else{
                            first_key += 10;
                        }
                    }else{
                        first_key += middle_value;
                    }
                }
            },
            handleRemove(file, fileList) {
                //console.log(file, fileList);
                let _this = this;
                deleteDeleteFile({path:this.imagePostData.path,'filename':file.name}).then(response => {
                    let type = 'error';
                    if( response.data.code == 1 ){
                        type = 'success';

                        // 删除fileList数据
                        for(let i in _this.fileList ){
                            if( _this.fileList[i].name == file.name ){
                                _this.fileList.splice(i,1);
                                break;
                            }
                        }
                    }

                    this.$message({
                        message: response.data.msg,
                        type
                    });
                }).catch( err => {
                    console.log( err );
                });
            },
            handlePreview(file) {
                console.log(file);
            },
            handleUploadSuccess(res, file , filelist ) {
                console.log(res);
                console.log(file);
                console.log(filelist);
                /*
                this.fileList.push({
                    name:file.name,
                    url:file.url
                });
                 */
                this.postForm.extra.prize_level[this.last_upload_index].prize_img = file.name;
                // = URL.createObjectURL(file.raw);
            },
            beforeAvatarUpload(file) {
                const isJPG = file.type === 'image/jpeg';
                const isPNG = file.type === 'image/png';
                const isLt2M = file.size / 1024 / 1024 < 2;

                if (!isJPG && !isPNG) {
                    this.$message.error('上传图片只能是 JPG或PNG 格式!');
                }
                if (!isLt2M) {
                    this.$message.error('上传图片大小不能超过 2MB!');
                }
                return (isJPG || isPNG) && isLt2M;
            },
            startUpload( scope ){
                this.last_upload_index = scope.$index;
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "res/sass/mixin.scss";

    .createPost-container {
        position: relative;

        .createPost-main-container {
            padding: 40px 45px 20px 50px;

            .postInfo-container {
                position: relative;
                @include clearfix;
                margin-bottom: 10px;

                .postInfo-container-item {
                    float: left;
                }
            }
        }

        .word-counter {
            width: 40px;
            position: absolute;
            right: 10px;
            top: 0px;
        }
    }

    .article-textarea /deep/ {
        textarea {
            padding-right: 40px;
            resize: none;
            border: none;
            border-radius: 0px;
            border-bottom: 1px solid #bfcbd9;
        }
    }

    /deep/ .sub-navbar{
        border-radius: 3px;
    }

    .container{
        margin-top:15px;
    }

    /deep/.el-upload-list--picture .el-upload-list__item {
        width: 250px;
        margin-right: 15px;
        margin-bottom: 15px;
        float:left;
    }
    .prize_img{
        vertical-align: middle;
        border:1px solid #DCDFE6;
        padding:5px;
        border-radius: 3px;
        width:70px;
        height:70px;
    }
    .upload-demo{
        box-sizing: border-box;
        display: inline-block;
        position: relative;
        margin-left: 15px;
    }
</style>
