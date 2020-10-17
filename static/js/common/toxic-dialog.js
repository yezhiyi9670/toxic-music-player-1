// Deprecated
function destroyalert(id)
{
    var wnd=$('#toxic-dialog-'+id);
    if(!wnd) return false;
    wnd.animate({
        "opacity": 0,
    },100,function(){wnd.remove();});
    return true;
}
function alert2(title,content,canclose=true)
{
    var wid=md5(Math.random().toString()).substring(0,16);
    var wnd=$(`
    <div class="toxic-dialog-cover" style="opacity:0;" id="toxic-dialog-${wid}" data-wid="${wid}">
        <div class="toxic-dialog-inner">
            <div class="toxic-dialog-title">${title}</div>
            ${canclose ? '<a class="toxic-dialog-close" href="javascript:void(0)" onclick="destroyalert(\''+wid+'\')">×</a>':''}
            <div class="toxic-dialog-content">
                ${content}
            </div>
        </div>
    </div>
    `);
    if(canclose) wnd.click(function(e){
        if(e.target===this) destroyalert(this.getAttribute('data-wid'));
        //console.log(e.target,this,this.getAttribute('data-wid'));
    });
    $('body').append(wnd);
    wnd.animate({
        "opacity": 1,
    },100);
    return wid;
}
