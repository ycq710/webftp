<script>
    function in_array(search,array){
        for(var i in array){
            if(array[i]==search){
                return true;
            }
        }
        return false;
    }
    /**
     * 自动选择文本框/文本域中的内容
     * @param  {dom} e [必填，必须是input或者textarea]
     * @param  {开始索引值} s [默认:0]
     * @param  {[type]} t [默认：总长度]
     * @return {[type]}   [null]
     */
    function autoselect(e,s,t){
        var startIndex = s ? Number(s) : 0;
        var stopIndex = t ? Number(t) : $(e).val().length;
        if (e.setSelectionRange) {
            e.setSelectionRange(startIndex, stopIndex);
        } else if (e.createTextRange) {
            var range = e.createTextRange();
            range.collapse(true);
            range.moveStart('character', startIndex);
            range.moveEnd('character', stopIndex - startIndex);
            range.select();
        }
        e.focus();
    }
</script>