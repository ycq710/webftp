<div class="common-footer aero">
    <span class="copyright-content">Powered by 酷牛云 v1.0 | Copyright ©
        <a href="https://www.kuniu.net/" target="_blank" draggable="false" style="color: #098ae6">kuniu.net</a>
        .<a href="javascript:void(0);" class="icon-info-sign copyright-bottom pl-5 tips_info_alert" draggable="false" style=""></a>
        <style>
            .tips_info{
                height: 75px;
                width: 330px;
                overflow: hidden;
                background: #5FB878;
                padding: 10px;
            }
        </style>
        <script>
            $('.tips_info_alert').click(function () {
                layui.use('layer', function(){
                    var layer = layui.layer;
                    layer.open({
                        type: 1,
                        shade: false,
                        title: false, //不显示标题
                        content: $('.tips_info'), //捕获的元素，注意：最好该指定的元素要存放在body最外层，否则可能被其它的相对元素所影响
                    });
                });
            });

        </script>
    </span>
</div>
<div class="tips_info" style="display: none;    color: #fff;">
    <p>1.大家好</p>
    <p>2.早上好</p>
    <p>3.感谢使用ftp</p>
</div>

</body>
</html>
