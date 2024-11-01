/*
* @author Motopress
* @license GPLv2 or later
*/
var styleParser=AFRAME.utils.styleParser;if("undefined"==typeof AFRAME)throw new Error("Component attempted to register before AFRAME was available.");AFRAME.registerComponent("event-set",{schema:{default:"",parse:function(e){var t=styleParser.parse(e),n={};return Object.keys(t).forEach(function(e){var i=e.replace(/([a-z])([A-Z])/g,"$1-$2").toLowerCase();n[i]=t[e]}),n}},multiple:!0,init:function(){this.eventHandler=null,this.eventName=null},update:function(e){this.removeEventListener(),this.updateEventListener(),this.addEventListener()},/**
   * Called when a component is removed (e.g., via removeAttribute).
   * Generally undoes all modifications to the entity.
   */
remove:function(){this.removeEventListener()},/**
   * Called when entity pauses.
   * Use to stop or remove any dynamic or background behavior such as events.
   */
pause:function(){this.removeEventListener()},/**
   * Called when entity resumes.
   * Use to continue or add any dynamic or background behavior such as events.
   */
play:function(){this.addEventListener()},/**
   * Update source-of-truth event listener registry.
   * Does not actually attach event listeners yet.
   */
updateEventListener:function(){var e=this.data,t=this.el,n=e._event,i=e._target;delete e._event,delete e._target;
// Decide the target to `setAttribute` on.
var r=i?t.sceneEl.querySelector(i):t;this.eventName=n,this.eventHandler=function(){
// Set attributes.
Object.keys(e).forEach(function(t){AFRAME.utils.entity.setComponentProperty.call(this,r,t,e[t])})}},addEventListener:function(){this.el.addEventListener(this.eventName,this.eventHandler)},removeEventListener:function(){this.el.removeEventListener(this.eventName,this.eventHandler)}});