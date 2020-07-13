<!DOCTYPE html>
<html lang="en">
<head>
    <title>ACE in Action</title>
    <style type="text/css" media="screen">
        #editor {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }
    </style>
</head>
<body>

<div id="editor">
    function foo(items) {
    var x = "All this is syntax highlighted";
    return x;
    }
</div>
<script src="/static/js/jquery-3.2.1.min.js"></script>
<script src="/static/js/lib/ace/src-min-noconflict/ace.js"></script>
<script>
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/monokai");
    editor.session.setMode("ace/mode/javascript");
</script>
</body>
</html>