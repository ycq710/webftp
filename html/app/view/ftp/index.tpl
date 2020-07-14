{include file="header"}
{include file="script"}
{include file="function"}
{include file="rmouse"}
<style>
    body{
        overflow-y: auto !important;
    }
    .item_box{
        float: left;
        height: 120px;
        margin: 10px;
        width: 80px;
        /*padding: 5px;*/
    }
    .item_box_bg{
        transition: 0.35s;
    }
    .item_box:hover .item_box_bg{
        background: #ffd987;
    }
    .item_box_bg_check{
        background: #0c884f!important;
    }
    .item_box img{
        height: 80px;
    }
    .item_box .item_name{
        text-align: center;
        white-space:normal;
        word-break: break-all;
        font-size: 14px;
    }
</style>
<div class="layui-row" style="height: 100%">
    <!-- 右侧内容 -->
    <div class="layui-col-lg12">

        <!-- 顶部导航 -->
        <link rel="stylesheet" href="/static/style/skin/base/app_explorer.css">
        <link rel="stylesheet" href="/static/style/font-awesome/css/font-awesome.css">
        <div class="frame-header" style="padding-left: 15px;">
            <style>
                .header-left .btn-default:active {
                    color: #000;
                    border-color: #888;
                }
                .header-left .btn-default:hover, .header-left .btn-default:focus {
                    color: #000;
                    border-color: #aaa;
                    -webkit-box-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
                    -moz-box-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
                    box-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
                }
                .header-middle .btn-default span{
                    position:relative !important;
                    top: -2px !important;
                }
                .icon-expand-alt {
                    display: inline-block;
                    background-image: url('../static/images/common/menu_icon.png');
                    width: 16px !important;
                    background-position: 0 -32px;
                    background-size: auto !important;
                    background-repeat: no-repeat;
                    height: 16px;
                }
                .icon-folder-close-alt {
                    display: inline-block;
                    background-image: url(../static/images/common/menu_icon.png);
                    width: 16px !important;
                    background-position: 0 -16px;
                    background-size: auto !important;
                    background-repeat: no-repeat;
                    height: 16px;
                }
                .icon-upload{
                    display: inline-block;
                    background-image: url(../static/images/common/menu_icon.png);
                    width: 16px !important;
                    background-position: 0 -48px;
                    background-size: auto !important;
                    background-repeat: no-repeat;
                    height: 16px;
                }
                button{
                    cursor: pointer
                }
                .btn:focus{
                    outline: none;
                    /*border: none;*/
                    border: 1px solid red;
                }
            </style>
            <div class="header-content">
                <div class="header-left">
                    <div class="btn-group btn-group-sm">
                        <button onclick="go_history()"  style=" border: solid 1px #ddd;" class="btn btn-default" id="btn-history-back" title="返回" type="button">
                            <i style="font-size: 1.25em; padding: 0 4px;" class="font-icon icon-angle-left"></i>
                            <span>返回</span>
                        </button>
                    </div>
                </div><!-- /header left -->

                <div class="header-middle">
                    <button onclick="window.location.href='/ftp/index.html' " class="btn btn-default btn-left-radius ml-10" id="home" title="回首页">
                        <i class="font-icon icon-home"></i>
                        <span style="top: -1px !important;">首页</span>
                    </button>

                    <button onclick="create_new_file_or_dir('file')" class="btn btn-default btn-left-radius ml-10" id="create_new_file" title="创建文件">
                        <i class="font-icon icon-expand-alt" style="color: rgba(255,255,255,0);"></i>
                        <span>创建文件</span>
                    </button>

                    <button onclick="create_new_file_or_dir('dir')" class="btn btn-default btn-left-radius ml-10" id="create_new_dir" title="新建目录">
                        <i class="font-icon icon-folder-close-alt" style="color: rgba(255,255,255,0);"></i>
                        <span >新建目录</span>
                    </button>

                    <form id="myForm"  enctype="multipart/form-data" style="display: none" >
                        <input id="up_file_input" type="file" name="up_file_name[]" multiple="multiple">
                    </form>

                    <button class="btn btn-default btn-left-radius ml-10" id="up_file" title="上传文件">
                        <i class="font-icon icon-upload" style="color: rgba(255,255,255,0);"></i>
                        <span >上传文件</span>
                    </button>


                   <button class="btn btn-default" id='refresh' onclick="window.location.reload()" title='刷新' type="button">
                        <i class="font-icon icon-refresh"></i>
                       <span >刷新</span>
                    </button>

                </div><!-- /header-middle end-->

                <div class="header-right" style="position: relative;left: 8px;top: 0">
                    <input  type="text" placeholder="输入搜索关键词" name="search_key" id="search_key" style="width: 180px;text-shadow: none;box-shadow: none; color: #5c5c5c;" class="btn-left-radius">
                    <button class="btn btn-default btn-right-radius" id="search_btn" title="搜索" type="button" style="position: relative;">
                        <i class="font-icon icon-search"></i>
                    </button>

                    <script>
                        function go_history(){
                            var is_top = getQueryString('dir');
                            if (is_top == '-1') {
                                layer.msg('再退就熄火了', {icon: 6});
                                return false;
                            }
                            window.history.go(-1);
                        }

                        function getQueryString(name) {
                            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                            var r = window.location.search.substr(1).match(reg);
                            if (r != null) return unescape(r[2]);
                            return '-1';
                        }

                        $('#up_file').click(function () {
                            $('#up_file_input').trigger('click');
                        });

                        $(document).on('change', "#up_file_input", function (e) {
                            var formData = new FormData($("#myForm")[0]);

                           
                            var file_size = $("#up_file_input")[0].files[0].size;
                            var sizeMb = (file_size / 1048576).toFixed(2);
                            if (sizeMb > 2) {
                                layer.msg('文件不能超过2MB', {
                                    icon: 5
                                });
                                return false;//大文件待做
                            }

                            formData.append('dir', "{:input('param.dir')}");
                            layer.msg('处理中...', {icon: 16,shade: 0.5});
                            $.ajax({
                                type: 'post',
                                dataType: "json",//预期服务器返回的数据类型
                                url:  "/action/up_file.html",
                                data: formData,
                                cache: false,
                                processData: false,
                                contentType: false,
                                success: function (res) {
                                layer.closeAll();
                                    if(res.code == 2){
                                        layer.msg(res.info, {icon: 2});
                                        return false;
                                    }
                                    layer.msg('文件上传成功', {icon: 1},function () {
                                        setTimeout(function () {
                                            window.location.reload();
                                        },500)
                                    });
                                },
                                error: function (res) {
                                    console.log(res);
                                }
                            });
                        });

                        //创建文件或目录
                        function create_new_file_or_dir(type){
                            if(type == 'file'){
                                var title = '<span>新建文件</span>';
                                var placeholder = '新的文件名，列如：test.txt ';
                            }else {
                                var title = '<span>新建目录</span>';
                                var placeholder = '新的目录';
                            }
                            layui.use('layer', function(){

                                var layer = layui.layer;
                                layer.config({
                                    extend: 'color/style.css' //加载您的扩展样式   ,skin:'layer-ext-color-dgreen'
                                });
                                layer.prompt(
                                    {
                                        title: title,
                                        formType: 3,
                                        skin:'layer-ext-color-green'
                                    },
                                    function(text, index){
                                        layer.msg('处理中...', {icon: 16,shade: 0.5});
                                        $.post(
                                            "/action/create.html",
                                            {
                                                create_name:text,
                                                type:type,
                                                dir:'{:input('param.dir')}'
                                            },
                                            function(result){
                                            layer.closeAll();
                                                layer.msg(result.info, {icon: result.code,time:1500},function () {
                                                    if(result.code !=2){
                                                        window.location.reload();
                                                    }
                                                });
                                            },'json'
                                        );

                                    }

                                );
                                $('body .layui-layer.layui-layer-page').addClass('layui-layer-prompt');
                            });

                        }


                        $('#search_btn').click(function () {
                            var key = $('#search_key').val();
                            alert('搜索关键词：'+key);
                        });
                    </script>
                </div><!-- /header right 搜索 -->


                <div class="header-right">
                    <button onclick="window.location.href='/action/login/out.html' " style="border-radius: 3px;cursor: pointer" class="btn btn-default btn-right-radius" id="login_out" title="退出" type="button">
                        <i class="font-icon icon-signout" style="color: #ff661e;"></i><span style="    position: relative; top: -2px; left: 1px;">退出</span>
                    </button>
                </div>

            </div>
        </div>
        <!-- 顶部导航完 -->
        <!-- 列表内容 -->
        <div class="layui-row r_mouse">
            {foreach name='files' item='v'}
                {if $v.type == 'file'}
                    <div class="item_box" data-is-dir="{$v.type}" data-isCanEdit="{$v.is_can_edit}" data-path="{$v.path}" data-filename="{$v.name}" data-time="{$v.month}-{$v.day}-{$v.time}" data-size="{$v.size}" data-type="{$v.file_type}">
                        <div class="item_box_bg">
                            <div class="item_img">
                                <img src="/static/images/file_icon/icon_file/{$v.file_type}.png" alt="">
                            </div>
                            <div class="item_name">
                                {$v.name}
                            </div>
                        </div>
                    </div>
                    {else}
                    <div class="item_box"  data-is-dir="{$v.type}" data-filename="{$v.name}" data-time="{$v.month}-{$v.day}-{$v.time}" data-size="{$v.size}" data-type="{$v.file_type}">
                        <div class="item_box_bg">
                            <div class="item_img">
                                <img src="/static/images/file_icon/icon_others/folder.png" alt="">
                            </div>
                            <div class="item_name">
                                {$v.name}
                            </div>
                        </div>
                    </div>
                {/if}
            {/foreach}
        </div>
        <!-- 列表内容完 -->

    </div>
    <!-- 右侧内容完 -->

</div>

<script>

    $('.item_box_bg').click(function (e) {
        e.stopPropagation();
        if ($(this).hasClass('item_box_bg_check')){
            $(this).removeClass('item_box_bg_check');
            $(this).children().eq(1).css('color','black');
        }else{
            $(this).addClass('item_box_bg_check');
            $(this).children().eq(1).css('color','white');
        }
        $(this).parent().siblings().children().removeClass('item_box_bg_check');
        $(this).parent().siblings().children().children('.item_name').css('color','black');
    });

    /*双击编辑文件信息*/
    layui.use('layer', function(){
        var layer = layui.layer;
        $('.item_box').dblclick(function(event) {
            event.preventDefault();
            var file_name =  $(this).attr('data-filename');
            var is_dir =  $(this).attr('data-is-dir');
            var file_type = $(this).attr('data-type');
            var isCanEdit = $(this).attr('data-isCanEdit');
            var file_path = "{:input('param.dir')}"+'/';
            var path = file_path+file_name;
            if(is_dir =='directory'){
                var now_dir = "{:input('param.dir')}";
                if(now_dir !== ''){
                    var url = '/ftp/index.html?'+'dir='+now_dir+'/'+file_name;
                }else {
                    var url = '/ftp/index.html?'+'dir='+file_name;
                }
                window.location.href = url;
                return false;
            }

            if(isCanEdit == '0'){
                layer.msg('该文件不支持编辑', {icon: 2});
                return  false;
            }

            layer.msg('加载中', {icon: 16,shade: 0.5});

            $.ajax({
                type: "post",
                url: "/action/getInfo.html",
                data: {
                    file_name:file_name,
                    dir:'{:input('param.dir')}'
                },
                dataType: "json",
                success: function(result){
                    layer.closeAll();
                    if(result.code == 2){
                        layer.msg(result.info, {icon: 2});
                        return false;
                    }
                    console.log(result);
                    var data = HtmlUtil.htmlEncode(result.data);
                    var html = '<div id="ace_conter" class="monokai">\n' +
                        '    <div class="ace_overall" style="top: 0;">\n' +
                        '        <div class="ace_editor_main" style="">\n' +
                        '            <div class="ace_conter_editor_local"  id="code_text">\n'
                        +data+
                        '            </div>\n' +
                        '            <div class="ace_conter_toolbar">\n' +
                        '                <div class="pull-left size_ellipsis">\n' +
                        '                    <span data-type="path" class="size_ellipsis">文件位置：<font title="">'+path+'</font></span>\n' +
                        '                </div>\n' +
                        '                <div class="pull-right">\n' +
                        '                    <span data-type="encoding">编码：UTF-8</span>\n' +
                        '                    <span data-type="lang">语言：<font>'+file_type+'</font></span>\n' +
                        '                    <span data-type="save_btn" class="save_btn">保存</span>\n' +
                        '                </div>\n' +
                        '            </div>\n' +
                        '        </div>\n' +
                        '    </div>\n' +
                        '    \n' +
                        '</div>\n';

                    var lay_title = "<span style='font-size: 16px; font-weight: 600;'>"+file_name+"</span>";
                    layer.open({
                        type: 1,
                        area: ['1200px', '800px'],
                        title:lay_title,
                        shade: 0.6 ,//遮罩透明度
                        maxmin: true, //允许全屏最小化
                        anim: 0,
                        content: html
                        // full: function() {
                        //     $('.layui-layer-content').css('height','100%');
                        // }
                    });


                    var editor = ace.edit("code_text");
                    editor.setTheme("ace/theme/monokai");
                    editor.setShowPrintMargin(false);
                    editor.setFontSize(14);

                    if(file_type == 'txt'){
                        file_type = 'text';
                    }
                    editor.session.setOptions({
                        mode: "ace/mode/"+file_type,
                        tabSize: 4,
                        useSoftTabs: true
                    });
                    document.onkeydown = keyDown;

                },
                error:function(result){
                    console.log(result);
                }
            });

        });
    });

    function keyDown(e){
        var currKey=0, e=e||event||window.event;
        currKey = e.keyCode||e.which||e.charCode;
        if(currKey == 83 && (e.ctrlKey||e.metaKey)){
            $('.save_btn').click();
            return false;
        }
    }
    /*双击编辑文件信息*/

    $('.layui-row').on('click',function (e) {
        e.stopPropagation();
        $(this).children().siblings().children().removeClass('item_box_bg_check');
        $(this).children().siblings().children().children('.item_name').css('color','black');
    });

    $(document).off('click').on('click','.save_btn',function () {
        var editor = ace.edit("code_text");
        var file_name = $('.layui-layer-title span').text();
        $.post(
            "/action/save.html",
            {
                file_name:file_name,
                save_info:editor.getValue(),
                dir:'{:input('param.dir')}'
            },
            function(result){
                layer.msg(result.info, {icon: result.code,time:1500});
            },'json'
        );
    });

</script>

{include file="footer"}
