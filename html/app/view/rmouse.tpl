<style>
    .r_mouse_file{
        box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        border-radius: 0;
        border: 1px solid rgba(0,0,0,0.1);
        background-clip: padding-box;
        margin: 0;
        width: 120px;
        font-size: 1em;
        display: inline-block;
        position: absolute;
        list-style-type: none;
        padding: 8px 0px;
        background: #fff;
        display: none;
        z-index: 999;
        -webkit-box-shadow: 0 10px 80px rgba(0,0,0,0.4);
        -moz-box-shadow: 0 10px 80px rgba(0,0,0,0.4);
    }

    .r_mouse_file .r_mouse_file_item{
        line-height: 25px;
        height: 25px;
        padding: 0 15px 0 20px;
        transition: .1s;
    }
    .r_mouse_file .r_mouse_file_item:hover{
        background: #0a7042;
        cursor: pointer;
        color: white;
    }
    .hide{
        display: none;
    }
</style>
<ul class="r_mouse_file file_mouse">
    <li class="r_mouse_file_item" data-action="edit"><i class="fa fa-edit fa-fw"></i>编辑</li>
    <li class="r_mouse_file_item" data-action="rname"><i class="fa fa-pencil fa-fw"></i>重命名</li>
    <li class="r_mouse_file_item" data-action="delete"><i class="fa fa-trash fa-fw"></i>删除</li>
    <li class="r_mouse_file_item hide" data-action="copy"><i class="fa fa-copy fa-fw"></i>复制</li>
    <li class="r_mouse_file_item hide" data-action="paste"><i class="fa fa-paste fa-fw"></i>粘贴</li>
    <li class="r_mouse_file_item" data-action="download"><i class="fa fa-cloud-download fa-fw "></i>下载文件</li>
</ul>
<script>
    $(function () {
        //阻止鼠标右键
        $('body').on('contextmenu',function (e) {
            return false;
        })
        //鼠标监听
        $('.r_mouse').click(function () {
            $('.r_mouse_file').hide();
        })
        //鼠标右键菜单
        var obj = '';
        $('.item_box').mousedown(function (e) {
            e.stopPropagation();
            $('.r_mouse_file').hide();
            if (e.which == 3){
                var xx = e.originalEvent.x || e.originalEvent.layerX || 0;
                var yy = e.originalEvent.y || e.originalEvent.layerY || 0;
                var is_edit = $(this).attr('data-iscanedit');
                var is_dir = $(this).attr('data-is-dir');
                obj = $(this);
                $('.file_mouse').show().css({
                    left:xx,
                    top:yy
                })
                if (is_edit == 1){
                    $('.file_mouse').children(':first').show();
                }else{
                    $('.file_mouse').children(':first').hide();
                }
                if (is_dir == 'directory'){
                    $('.file_mouse').children(':last').hide();
                }else{
                    $('.file_mouse').children(':last').show();
                }
            }else{
                $('.r_mouse_file').hide();
            }
        })
        //隐藏上一个鼠标右键的菜单，鼠标监听
        $('.layui-row').mousedown(function (e) {
            if (e.which == 3){
                $('.r_mouse_file').hide();
            }else{
                $('.r_mouse_file').hide();
            }
        })
        //右键菜单点击事件
        var file_old_name = '';
        $('.r_mouse_file .r_mouse_file_item').click(function () {
            $('.r_mouse_file').hide();
            var action = $(this).attr('data-action');
            //文件名称
            var file_name = $.trim(obj.children().children('.item_name').html());
            file_old_name = file_name;
            if (action == 'rname'){
                //截取字符长度
                var name_len = file_name.split('.')[0].length;
                obj.children().children('.item_name').html('<textarea id="auto_f" onkeydown="if(event.keyCode==13)return false;" autofocus="autofocus" style="width: 100px" start="0" end="'+name_len+'">'+file_name+'</textarea>');
                $('#auto_f').focus();
            }else if(action == 'edit'){
                //触发编辑双击
                obj.dblclick();
            }else{
                //对操作进行请求
                layer.confirm('确认当前操作？', {
                    skin:'layer-ext-color-red',
                    btn: ['确定','取消'] //按钮
                }, function(){
                    //对操作进行请求
                    $.ajax({
                        url:"/action/"+action+'.html',
                        type:'post',
                        dataType:'json',
                        data:{
                            file_name:file_name,
                            dir:"{:input('param.dir')}"
                        },success:function (data) {
                            if (action == 'download'){
                                location.href = data.data;
                            }else{
                                layer.msg(data.info,{icon:data.code,time:1500},function () {
                                    location.reload();
                                });
                            }
                        }

                    })
                })
            }
        })
        //定义ESC不弹出layui的确认框，原来是失去焦点就会弹出确认重命名的确认框
        var window_show =true;
        //监听键盘事件
        $(document).keyup(function(e){
            var key =  e.which || e.keyCode;
            var content = $('#auto_f').val();
            if(key == 27){
                //ESC
                window_show = false;
                $('#auto_f').off('blur');
                $('#auto_f').parent().html(content);
                layer.closeAll();
            }else if (key == 13){
                //回车
                window_show = true;
                //获取创建文件或者目录的名称
                var create_file_dir = $('.layui-layer-input').val();
                //判断元素是否存在
                if ($('.layui-layer-dialog').length == 1 || ($('.layui-layer-prompt').length == 1 && create_file_dir.length > 0) ){
                    //给元素绑定点击事件
                    $('.layui-layer-btn0').trigger('click');
                }else{
                    //重命名文本域失去焦点
                    $('#auto_f').blur();
                }

            }
        });
        layui.use('layer', function(){
            var layer = layui.layer
            layer.config({
                extend: 'color/style.css' //加载您的扩展样式
            });
            //监听回车
            //重命名获取焦点
            $(document).off('foucus').on("focus",'#auto_f',function(e){
                var start = $(this).attr("start");
                var end = $(this).attr("end");
                window_show = true;
                autoselect(this, start , end);
            }).on('blur','#auto_f',function () {
                console.log($(this));
                //重命名失去焦点
                var new_name = $.trim($(this).val());
                var obj = $(this);
                //ESC阻止默认弹框
                if (window_show == false){
                    return false;
                }
                layer.confirm('确认重命名当前文件？', {
                    skin:'layer-ext-color-green',
                    btn: ['确定','取消'] //按钮
                }, function(){
                    if (file_old_name == new_name){
                        layer.msg('无效操作！',{icon:0,time:1500},function () {
                            obj.parent().html(file_old_name);
                        });
                        return false;
                    }
                    //请求重命名
                    $.ajax({
                        url:"/action/rename.html",
                        type:'post',
                        dataType:'json',
                        data:{
                            //重命名后的名称
                            new_name:new_name,
                            //原本的名称
                            file_name:file_old_name,
                            //获取地址栏的dir路径
                            dir:"{:input('param.dir')}"
                        },success:function (data) {
                            $('.r_mouse_file').hide();
                            layer.msg(data.info,{icon:data.code,time:1500},function () {
                                location.reload();
                            })
                        }

                    })
                },function () {
                    //取消的时候要还原原本的名字
                    obj.parent().html(file_old_name);
                })

            });
        })

    })
</script>
