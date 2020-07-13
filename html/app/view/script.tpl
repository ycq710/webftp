<!--代码预览编辑器-->
<style>
    .ace_overall {
        width: 100%;
        display: inline-block;
        position: absolute;
        bottom: 0;
        background: #777;
        transition: top 500ms;
    }
    .ace_editor_main {
        position: relative;
        background: #444;
        transition: margin 500ms;
        height: 100%;
    }
    .ace_conter_editor_local {
        background: #333;
        height: 721px;
        overflow-y: auto;
    }

    .ace_conter_toolbar {
        height: 35px;
        line-height: 35px;
        bottom: 0px;
        right: 0;
        left: 0;
        padding-right: 15px;
        text-align: right;
        position: absolute;
        background: #444;
        font-size: 0;
        overflow: hidden;
        transition: all 500ms;
    }

    .ace_conter_toolbar .pull-left, .ace_conter_toolbar .pull-right {
        height: 35px;
    }
    .ace_conter_toolbar .pull-left {
        text-align: left;
        max-width: 50%;
    }
    .size_ellipsis {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pull-left {
        float: left!important;
    }

    .pull-right {
        float: right!important;
    }

    .ace_conter_toolbar .pull-left span, .ace_conter_toolbar .pull-right span {
        color: #fff;
        display: inline-block;
        border-right: 1px solid #505050;
        padding: 0 15px;
        cursor: pointer;
        transition: all 500ms;
        font-size: 13px;
    }

    .save_btn{
        background: #5084ae;
    }
    .save_btn:hover{
        background: #2196F3;
    }
</style>
<script src="/static/js/lib/ace/src-min-noconflict/ace.js"></script>
<script src="/static/js/lib/ace/src-min-noconflict/ext-language_tools.js"></script>
<!--代码预览编辑器完-->

<script>
    //对code字符串进行转码
    var HtmlUtil = {
        /*1.用浏览器内部转换器实现html转码*/
        htmlEncode:function (html){
            //1.首先动态创建一个容器标签元素，如DIV
            var temp = document.createElement ("div");
            //2.然后将要转换的字符串设置为这个元素的innerText(ie支持)或者textContent(旧版火狐，google支持)
            (temp.textContent != undefined ) ? (temp.textContent = html) : (temp.innerText = html);
            //3.最后返回这个元素的innerHTML，即得到经过HTML编码转换的字符串了
            var output = temp.innerHTML;
            temp = null;
            return output;
        },
        /*2.用浏览器内部转换器实现html解码*/
        htmlDecode:function (text){
            //1.首先动态创建一个容器标签元素，如DIV
            var temp = document.createElement("div");
            //2.然后将要转换的字符串设置为这个元素的innerHTML(ie，火狐，google都支持)
            temp.innerHTML = text;
            //3.最后返回这个元素的innerText(ie支持)或者textContent(火狐，google支持)，即得到经过HTML解码的字符串了。
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        },
        /*3.用正则表达式实现html转码*/
        htmlEncodeByRegExp:function (str){
            var s = "";
            if(str.length == 0) return "";
            s = str.replace(/&/g,"&amp;");
            s = s.replace(/</g,"&lt;");
            s = s.replace(/>/g,"&gt;");
            s = s.replace(/\s/g,"&nbsp;");
            s = s.replace(/\'/g,"&#39;");
            s = s.replace(/\"/g,"&quot;");
            return s;
        },
        /*4.用正则表达式实现html解码*/
        htmlDecodeByRegExp:function (str){
            var s = "";
            if(str.length == 0) return "";
            s = str.replace(/&amp;/g,"&");
            s = s.replace(/&lt;/g,"<");
            s = s.replace(/&gt;/g,">");
            s = s.replace(/&nbsp;/g," ");
            s = s.replace(/&#39;/g,"\'");
            s = s.replace(/&quot;/g,"\"");
            return s;
        }
    };
</script>