/*
* @author Motopress
* @license GPLv2 or later
*/
AFRAME.registerComponent("set-image",{schema:{on:{type:"string"},target:{type:"selector"},src:{type:"string"},w:{type:"number",default:1},h:{type:"number",default:1},r:{type:"string"},dur:{type:"number",default:300}},init:function(){var e=this.data,t=this.el;this.setupFadeAnimation(),t.addEventListener(e.on,function(t){
// Fade out image.
e.target.emit("set-image-fade");var n=e.w/50,r=e.h/50,i=-1*t.target.object3D.matrixWorld.elements[14],a=t.target.object3D.matrixWorld.elements[12];setTimeout(function(){
// Set image.
e.target.setAttribute("material","src",e.src),e.target.setAttribute("geometry","width",n),e.target.setAttribute("geometry","height",r),e.target.setAttribute("position",i+" 10 "+a),e.target.setAttribute("rotation","20 "+e.r+" 0")},e.dur)})},/**
	 * Setup fade-in + fade-out.
	 */
setupFadeAnimation:function(){var e=this.data,t=this.data.target;
// Only set up once.
t.dataset.setImageFadeSetup||(t.dataset.setImageFadeSetup=!0,
// Create animation.
t.setAttribute("animation__fade",{property:"material.color",startEvents:"set-image-fade",dir:"alternate",dur:e.dur,from:"#FFF",to:"#000"}))}});