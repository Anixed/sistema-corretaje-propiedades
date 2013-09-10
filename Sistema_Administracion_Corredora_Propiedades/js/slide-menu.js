//<![CDATA[
//jQuery.noConflict();
var arrowimages = {down:['down-arrowclass', 'images/arrow-down.gif', 23], right:['right-arrowclass', 'images/arrow-right.gif']}
var jqueryslidemenu = {
animateduration: {over: 200, out: 100},
buildmenu:function(menuid, arrowsvar){
jQuery(document).ready(function(jQuery){
var jQuerymainmenu=jQuery("#"+menuid+">ul")
var jQueryheaders=jQuerymainmenu.find("ul").parent()
jQueryheaders.each(function(i){
var jQuerycurobj=jQuery(this)
var jQuerysubul=jQuery(this).find('ul:eq(0)')
this._dimensions={w:this.offsetWidth, h:this.offsetHeight, subulw:jQuerysubul.outerWidth(), subulh:jQuerysubul.outerHeight()}
this.istopheader=jQuerycurobj.parents("ul").length==1? true : false
jQuerysubul.css({top:this.istopheader? this._dimensions.h+"px" : 0})
jQuerycurobj.children("a:eq(0)").css(this.istopheader? {paddingRight: arrowsvar.down[2]} : {}).append(
'<img src="'+ (this.istopheader? arrowsvar.down[1] : arrowsvar.right[1])
+'" class="' + (this.istopheader? arrowsvar.down[0] : arrowsvar.right[0])
+ '" style="border:0;" />')
jQuerycurobj.hover(function(e){
var jQuerytargetul=jQuery(this).children("ul:eq(0)")
this._offsets={left:jQuery(this).offset().left, top:jQuery(this).offset().top}
var menuleft=this.istopheader? 0 : this._dimensions.w
menuleft=(this._offsets.left+menuleft+this._dimensions.subulw>jQuery(window).width())? (this.istopheader? -this._dimensions.subulw+this._dimensions.w : -this._dimensions.w) : menuleft
if (jQuerytargetul.queue().length<=1)
jQuerytargetul.css({left:menuleft+"px", width:this._dimensions.subulw+'px'}).slideDown(jqueryslidemenu.animateduration.over)},
function(e){
var jQuerytargetul=jQuery(this).children("ul:eq(0)")
jQuerytargetul.slideUp(jqueryslidemenu.animateduration.out)})})
jQuerymainmenu.find("ul").css({display:'none', visibility:'visible'})})}}
jqueryslidemenu.buildmenu("slide-menu", arrowimages)
//]]>