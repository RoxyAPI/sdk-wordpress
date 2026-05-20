"use strict";var RoxyUI=(()=>{var Je=Object.defineProperty;var Dt=Object.getOwnPropertyDescriptor;var mr=Object.getOwnPropertyNames;var hr=Object.prototype.hasOwnProperty;var gr=(n,a)=>{for(var e in a)Je(n,e,{get:a[e],enumerable:!0})},ur=(n,a,e,t)=>{if(a&&typeof a=="object"||typeof a=="function")for(let r of mr(a))!hr.call(n,r)&&r!==e&&Je(n,r,{get:()=>a[r],enumerable:!(t=Dt(a,r))||t.enumerable});return n};var yr=n=>ur(Je({},"__esModule",{value:!0}),n),p=(n,a,e,t)=>{for(var r=t>1?void 0:t?Dt(a,e):a,o=n.length-1,i;o>=0;o--)(i=n[o])&&(r=(t?i(a,e,r):i(r))||r);return t&&r&&Je(a,e,r),r};var xa={};gr(xa,{ROXY_COMPONENTS:()=>ct,ROXY_UI_COMPONENTS:()=>ya,ROXY_UI_VERSION:()=>pr,RoxyAshtakavargaGrid:()=>Y,RoxyBiorhythmChart:()=>U,RoxyChoghadiyaGrid:()=>me,RoxyCompatibilityCard:()=>F,RoxyDashaTimeline:()=>W,RoxyData:()=>J,RoxyDivisionalChart:()=>Z,RoxyDoshaCard:()=>Q,RoxyEndpointForm:()=>H,RoxyGunaMilan:()=>he,RoxyHexagram:()=>ee,RoxyHoroscopeCard:()=>te,RoxyKpChart:()=>re,RoxyKpPlanetsTable:()=>ge,RoxyKpRulingPlanets:()=>ue,RoxyLocationSearch:()=>D,RoxyMoonPhase:()=>ae,RoxyNakshatraCard:()=>ye,RoxyNatalChart:()=>q,RoxyNumerologyCard:()=>se,RoxyPanchangTable:()=>oe,RoxyShadbalaTable:()=>xe,RoxySynastryChart:()=>fe,RoxyTarotCard:()=>ne,RoxyTarotSpread:()=>ie,RoxyTransitsTable:()=>be,RoxyVedicKundli:()=>le,RoxyVedicPlanetsTable:()=>ve,RoxyWesternPlanetsTable:()=>$e,RoxyYogaList:()=>de});var Xe=globalThis,Ze=Xe.ShadowRoot&&(Xe.ShadyCSS===void 0||Xe.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,pt=Symbol(),Ot=new WeakMap,Oe=class{constructor(a,e,t){if(this._$cssResult$=!0,t!==pt)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=a,this.t=e}get styleSheet(){let a=this.o,e=this.t;if(Ze&&a===void 0){let t=e!==void 0&&e.length===1;t&&(a=Ot.get(e)),a===void 0&&((this.o=a=new CSSStyleSheet).replaceSync(this.cssText),t&&Ot.set(e,a))}return a}toString(){return this.cssText}},Ht=n=>new Oe(typeof n=="string"?n:n+"",void 0,pt),f=(n,...a)=>{let e=n.length===1?n[0]:a.reduce((t,r,o)=>t+(i=>{if(i._$cssResult$===!0)return i.cssText;if(typeof i=="number")return i;throw Error("Value passed to 'css' function must be a 'css' function result: "+i+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(r)+n[o+1],n[0]);return new Oe(e,n,pt)},Gt=(n,a)=>{if(Ze)n.adoptedStyleSheets=a.map(e=>e instanceof CSSStyleSheet?e:e.styleSheet);else for(let e of a){let t=document.createElement("style"),r=Xe.litNonce;r!==void 0&&t.setAttribute("nonce",r),t.textContent=e.cssText,n.appendChild(t)}},mt=Ze?n=>n:n=>n instanceof CSSStyleSheet?(a=>{let e="";for(let t of a.cssRules)e+=t.cssText;return Ht(e)})(n):n;var{is:xr,defineProperty:fr,getOwnPropertyDescriptor:br,getOwnPropertyNames:vr,getOwnPropertySymbols:$r,getPrototypeOf:wr}=Object,Qe=globalThis,jt=Qe.trustedTypes,kr=jt?jt.emptyScript:"",Sr=Qe.reactiveElementPolyfillSupport,He=(n,a)=>n,Ge={toAttribute(n,a){switch(a){case Boolean:n=n?kr:null;break;case Object:case Array:n=n==null?n:JSON.stringify(n)}return n},fromAttribute(n,a){let e=n;switch(a){case Boolean:e=n!==null;break;case Number:e=n===null?null:Number(n);break;case Object:case Array:try{e=JSON.parse(n)}catch{e=null}}return e}},et=(n,a)=>!xr(n,a),It={attribute:!0,type:String,converter:Ge,reflect:!1,useDefault:!1,hasChanged:et};Symbol.metadata??=Symbol("metadata"),Qe.litPropertyMetadata??=new WeakMap;var V=class extends HTMLElement{static addInitializer(a){this._$Ei(),(this.l??=[]).push(a)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(a,e=It){if(e.state&&(e.attribute=!1),this._$Ei(),this.prototype.hasOwnProperty(a)&&((e=Object.create(e)).wrapped=!0),this.elementProperties.set(a,e),!e.noAccessor){let t=Symbol(),r=this.getPropertyDescriptor(a,t,e);r!==void 0&&fr(this.prototype,a,r)}}static getPropertyDescriptor(a,e,t){let{get:r,set:o}=br(this.prototype,a)??{get(){return this[e]},set(i){this[e]=i}};return{get:r,set(i){let d=r?.call(this);o?.call(this,i),this.requestUpdate(a,d,t)},configurable:!0,enumerable:!0}}static getPropertyOptions(a){return this.elementProperties.get(a)??It}static _$Ei(){if(this.hasOwnProperty(He("elementProperties")))return;let a=wr(this);a.finalize(),a.l!==void 0&&(this.l=[...a.l]),this.elementProperties=new Map(a.elementProperties)}static finalize(){if(this.hasOwnProperty(He("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(He("properties"))){let e=this.properties,t=[...vr(e),...$r(e)];for(let r of t)this.createProperty(r,e[r])}let a=this[Symbol.metadata];if(a!==null){let e=litPropertyMetadata.get(a);if(e!==void 0)for(let[t,r]of e)this.elementProperties.set(t,r)}this._$Eh=new Map;for(let[e,t]of this.elementProperties){let r=this._$Eu(e,t);r!==void 0&&this._$Eh.set(r,e)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(a){let e=[];if(Array.isArray(a)){let t=new Set(a.flat(1/0).reverse());for(let r of t)e.unshift(mt(r))}else a!==void 0&&e.push(mt(a));return e}static _$Eu(a,e){let t=e.attribute;return t===!1?void 0:typeof t=="string"?t:typeof a=="string"?a.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){this._$ES=new Promise(a=>this.enableUpdating=a),this._$AL=new Map,this._$E_(),this.requestUpdate(),this.constructor.l?.forEach(a=>a(this))}addController(a){(this._$EO??=new Set).add(a),this.renderRoot!==void 0&&this.isConnected&&a.hostConnected?.()}removeController(a){this._$EO?.delete(a)}_$E_(){let a=new Map,e=this.constructor.elementProperties;for(let t of e.keys())this.hasOwnProperty(t)&&(a.set(t,this[t]),delete this[t]);a.size>0&&(this._$Ep=a)}createRenderRoot(){let a=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return Gt(a,this.constructor.elementStyles),a}connectedCallback(){this.renderRoot??=this.createRenderRoot(),this.enableUpdating(!0),this._$EO?.forEach(a=>a.hostConnected?.())}enableUpdating(a){}disconnectedCallback(){this._$EO?.forEach(a=>a.hostDisconnected?.())}attributeChangedCallback(a,e,t){this._$AK(a,t)}_$ET(a,e){let t=this.constructor.elementProperties.get(a),r=this.constructor._$Eu(a,t);if(r!==void 0&&t.reflect===!0){let o=(t.converter?.toAttribute!==void 0?t.converter:Ge).toAttribute(e,t.type);this._$Em=a,o==null?this.removeAttribute(r):this.setAttribute(r,o),this._$Em=null}}_$AK(a,e){let t=this.constructor,r=t._$Eh.get(a);if(r!==void 0&&this._$Em!==r){let o=t.getPropertyOptions(r),i=typeof o.converter=="function"?{fromAttribute:o.converter}:o.converter?.fromAttribute!==void 0?o.converter:Ge;this._$Em=r;let d=i.fromAttribute(e,o.type);this[r]=d??this._$Ej?.get(r)??d,this._$Em=null}}requestUpdate(a,e,t,r=!1,o){if(a!==void 0){let i=this.constructor;if(r===!1&&(o=this[a]),t??=i.getPropertyOptions(a),!((t.hasChanged??et)(o,e)||t.useDefault&&t.reflect&&o===this._$Ej?.get(a)&&!this.hasAttribute(i._$Eu(a,t))))return;this.C(a,e,t)}this.isUpdatePending===!1&&(this._$ES=this._$EP())}C(a,e,{useDefault:t,reflect:r,wrapped:o},i){t&&!(this._$Ej??=new Map).has(a)&&(this._$Ej.set(a,i??e??this[a]),o!==!0||i!==void 0)||(this._$AL.has(a)||(this.hasUpdated||t||(e=void 0),this._$AL.set(a,e)),r===!0&&this._$Em!==a&&(this._$Eq??=new Set).add(a))}async _$EP(){this.isUpdatePending=!0;try{await this._$ES}catch(e){Promise.reject(e)}let a=this.scheduleUpdate();return a!=null&&await a,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??=this.createRenderRoot(),this._$Ep){for(let[r,o]of this._$Ep)this[r]=o;this._$Ep=void 0}let t=this.constructor.elementProperties;if(t.size>0)for(let[r,o]of t){let{wrapped:i}=o,d=this[r];i!==!0||this._$AL.has(r)||d===void 0||this.C(r,void 0,o,d)}}let a=!1,e=this._$AL;try{a=this.shouldUpdate(e),a?(this.willUpdate(e),this._$EO?.forEach(t=>t.hostUpdate?.()),this.update(e)):this._$EM()}catch(t){throw a=!1,this._$EM(),t}a&&this._$AE(e)}willUpdate(a){}_$AE(a){this._$EO?.forEach(e=>e.hostUpdated?.()),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(a)),this.updated(a)}_$EM(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(a){return!0}update(a){this._$Eq&&=this._$Eq.forEach(e=>this._$ET(e,this[e])),this._$EM()}updated(a){}firstUpdated(a){}};V.elementStyles=[],V.shadowRootOptions={mode:"open"},V[He("elementProperties")]=new Map,V[He("finalized")]=new Map,Sr?.({ReactiveElement:V}),(Qe.reactiveElementVersions??=[]).push("2.1.2");var bt=globalThis,Bt=n=>n,tt=bt.trustedTypes,Kt=tt?tt.createPolicy("lit-html",{createHTML:n=>n}):void 0,Wt="$lit$",ce=`lit$${Math.random().toFixed(9).slice(2)}$`,Jt="?"+ce,Ar=`<${Jt}>`,Ae=document,Ie=()=>Ae.createComment(""),Be=n=>n===null||typeof n!="object"&&typeof n!="function",vt=Array.isArray,Cr=n=>vt(n)||typeof n?.[Symbol.iterator]=="function",ht=`[ 	
\f\r]`,je=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,qt=/-->/g,Vt=/>/g,ke=RegExp(`>|${ht}(?:([^\\s"'>=/]+)(${ht}*=${ht}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),Yt=/'/g,Ut=/"/g,Xt=/^(?:script|style|textarea|title)$/i,$t=n=>(a,...e)=>({_$litType$:n,strings:a,values:e}),s=$t(1),k=$t(2),ka=$t(3),Ce=Symbol.for("lit-noChange"),l=Symbol.for("lit-nothing"),Ft=new WeakMap,Se=Ae.createTreeWalker(Ae,129);function Zt(n,a){if(!vt(n)||!n.hasOwnProperty("raw"))throw Error("invalid template strings array");return Kt!==void 0?Kt.createHTML(a):a}var Er=(n,a)=>{let e=n.length-1,t=[],r,o=a===2?"<svg>":a===3?"<math>":"",i=je;for(let d=0;d<e;d++){let c=n[d],m,g,h=-1,y=0;for(;y<c.length&&(i.lastIndex=y,g=i.exec(c),g!==null);)y=i.lastIndex,i===je?g[1]==="!--"?i=qt:g[1]!==void 0?i=Vt:g[2]!==void 0?(Xt.test(g[2])&&(r=RegExp("</"+g[2],"g")),i=ke):g[3]!==void 0&&(i=ke):i===ke?g[0]===">"?(i=r??je,h=-1):g[1]===void 0?h=-2:(h=i.lastIndex-g[2].length,m=g[1],i=g[3]===void 0?ke:g[3]==='"'?Ut:Yt):i===Ut||i===Yt?i=ke:i===qt||i===Vt?i=je:(i=ke,r=void 0);let S=i===ke&&n[d+1].startsWith("/>")?" ":"";o+=i===je?c+Ar:h>=0?(t.push(m),c.slice(0,h)+Wt+c.slice(h)+ce+S):c+ce+(h===-2?d:S)}return[Zt(n,o+(n[e]||"<?>")+(a===2?"</svg>":a===3?"</math>":"")),t]},Ke=class n{constructor({strings:a,_$litType$:e},t){let r;this.parts=[];let o=0,i=0,d=a.length-1,c=this.parts,[m,g]=Er(a,e);if(this.el=n.createElement(m,t),Se.currentNode=this.el.content,e===2||e===3){let h=this.el.content.firstChild;h.replaceWith(...h.childNodes)}for(;(r=Se.nextNode())!==null&&c.length<d;){if(r.nodeType===1){if(r.hasAttributes())for(let h of r.getAttributeNames())if(h.endsWith(Wt)){let y=g[i++],S=r.getAttribute(h).split(ce),w=/([.?@])?(.*)/.exec(y);c.push({type:1,index:o,name:w[2],strings:S,ctor:w[1]==="."?ut:w[1]==="?"?yt:w[1]==="@"?xt:Re}),r.removeAttribute(h)}else h.startsWith(ce)&&(c.push({type:6,index:o}),r.removeAttribute(h));if(Xt.test(r.tagName)){let h=r.textContent.split(ce),y=h.length-1;if(y>0){r.textContent=tt?tt.emptyScript:"";for(let S=0;S<y;S++)r.append(h[S],Ie()),Se.nextNode(),c.push({type:2,index:++o});r.append(h[y],Ie())}}}else if(r.nodeType===8)if(r.data===Jt)c.push({type:2,index:o});else{let h=-1;for(;(h=r.data.indexOf(ce,h+1))!==-1;)c.push({type:7,index:o}),h+=ce.length-1}o++}}static createElement(a,e){let t=Ae.createElement("template");return t.innerHTML=a,t}};function Te(n,a,e=n,t){if(a===Ce)return a;let r=t!==void 0?e._$Co?.[t]:e._$Cl,o=Be(a)?void 0:a._$litDirective$;return r?.constructor!==o&&(r?._$AO?.(!1),o===void 0?r=void 0:(r=new o(n),r._$AT(n,e,t)),t!==void 0?(e._$Co??=[])[t]=r:e._$Cl=r),r!==void 0&&(a=Te(n,r._$AS(n,a.values),r,t)),a}var gt=class{constructor(a,e){this._$AV=[],this._$AN=void 0,this._$AD=a,this._$AM=e}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(a){let{el:{content:e},parts:t}=this._$AD,r=(a?.creationScope??Ae).importNode(e,!0);Se.currentNode=r;let o=Se.nextNode(),i=0,d=0,c=t[0];for(;c!==void 0;){if(i===c.index){let m;c.type===2?m=new qe(o,o.nextSibling,this,a):c.type===1?m=new c.ctor(o,c.name,c.strings,this,a):c.type===6&&(m=new ft(o,this,a)),this._$AV.push(m),c=t[++d]}i!==c?.index&&(o=Se.nextNode(),i++)}return Se.currentNode=Ae,r}p(a){let e=0;for(let t of this._$AV)t!==void 0&&(t.strings!==void 0?(t._$AI(a,t,e),e+=t.strings.length-2):t._$AI(a[e])),e++}},qe=class n{get _$AU(){return this._$AM?._$AU??this._$Cv}constructor(a,e,t,r){this.type=2,this._$AH=l,this._$AN=void 0,this._$AA=a,this._$AB=e,this._$AM=t,this.options=r,this._$Cv=r?.isConnected??!0}get parentNode(){let a=this._$AA.parentNode,e=this._$AM;return e!==void 0&&a?.nodeType===11&&(a=e.parentNode),a}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(a,e=this){a=Te(this,a,e),Be(a)?a===l||a==null||a===""?(this._$AH!==l&&this._$AR(),this._$AH=l):a!==this._$AH&&a!==Ce&&this._(a):a._$litType$!==void 0?this.$(a):a.nodeType!==void 0?this.T(a):Cr(a)?this.k(a):this._(a)}O(a){return this._$AA.parentNode.insertBefore(a,this._$AB)}T(a){this._$AH!==a&&(this._$AR(),this._$AH=this.O(a))}_(a){this._$AH!==l&&Be(this._$AH)?this._$AA.nextSibling.data=a:this.T(Ae.createTextNode(a)),this._$AH=a}$(a){let{values:e,_$litType$:t}=a,r=typeof t=="number"?this._$AC(a):(t.el===void 0&&(t.el=Ke.createElement(Zt(t.h,t.h[0]),this.options)),t);if(this._$AH?._$AD===r)this._$AH.p(e);else{let o=new gt(r,this),i=o.u(this.options);o.p(e),this.T(i),this._$AH=o}}_$AC(a){let e=Ft.get(a.strings);return e===void 0&&Ft.set(a.strings,e=new Ke(a)),e}k(a){vt(this._$AH)||(this._$AH=[],this._$AR());let e=this._$AH,t,r=0;for(let o of a)r===e.length?e.push(t=new n(this.O(Ie()),this.O(Ie()),this,this.options)):t=e[r],t._$AI(o),r++;r<e.length&&(this._$AR(t&&t._$AB.nextSibling,r),e.length=r)}_$AR(a=this._$AA.nextSibling,e){for(this._$AP?.(!1,!0,e);a!==this._$AB;){let t=Bt(a).nextSibling;Bt(a).remove(),a=t}}setConnected(a){this._$AM===void 0&&(this._$Cv=a,this._$AP?.(a))}},Re=class{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(a,e,t,r,o){this.type=1,this._$AH=l,this._$AN=void 0,this.element=a,this.name=e,this._$AM=r,this.options=o,t.length>2||t[0]!==""||t[1]!==""?(this._$AH=Array(t.length-1).fill(new String),this.strings=t):this._$AH=l}_$AI(a,e=this,t,r){let o=this.strings,i=!1;if(o===void 0)a=Te(this,a,e,0),i=!Be(a)||a!==this._$AH&&a!==Ce,i&&(this._$AH=a);else{let d=a,c,m;for(a=o[0],c=0;c<o.length-1;c++)m=Te(this,d[t+c],e,c),m===Ce&&(m=this._$AH[c]),i||=!Be(m)||m!==this._$AH[c],m===l?a=l:a!==l&&(a+=(m??"")+o[c+1]),this._$AH[c]=m}i&&!r&&this.j(a)}j(a){a===l?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,a??"")}},ut=class extends Re{constructor(){super(...arguments),this.type=3}j(a){this.element[this.name]=a===l?void 0:a}},yt=class extends Re{constructor(){super(...arguments),this.type=4}j(a){this.element.toggleAttribute(this.name,!!a&&a!==l)}},xt=class extends Re{constructor(a,e,t,r,o){super(a,e,t,r,o),this.type=5}_$AI(a,e=this){if((a=Te(this,a,e,0)??l)===Ce)return;let t=this._$AH,r=a===l&&t!==l||a.capture!==t.capture||a.once!==t.once||a.passive!==t.passive,o=a!==l&&(t===l||r);r&&this.element.removeEventListener(this.name,this,t),o&&this.element.addEventListener(this.name,this,a),this._$AH=a}handleEvent(a){typeof this._$AH=="function"?this._$AH.call(this.options?.host??this.element,a):this._$AH.handleEvent(a)}},ft=class{constructor(a,e,t){this.element=a,this.type=6,this._$AN=void 0,this._$AM=e,this.options=t}get _$AU(){return this._$AM._$AU}_$AI(a){Te(this,a)}};var Lr=bt.litHtmlPolyfillSupport;Lr?.(Ke,qe),(bt.litHtmlVersions??=[]).push("3.3.2");var Qt=(n,a,e)=>{let t=e?.renderBefore??a,r=t._$litPart$;if(r===void 0){let o=e?.renderBefore??null;t._$litPart$=r=new qe(a.insertBefore(Ie(),o),o,void 0,e??{})}return r._$AI(n),r};var wt=globalThis,x=class extends V{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){let a=super.createRenderRoot();return this.renderOptions.renderBefore??=a.firstChild,a}update(a){let e=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(a),this._$Do=Qt(e,this.renderRoot,this.renderOptions)}connectedCallback(){super.connectedCallback(),this._$Do?.setConnected(!0)}disconnectedCallback(){super.disconnectedCallback(),this._$Do?.setConnected(!1)}render(){return Ce}};x._$litElement$=!0,x.finalized=!0,wt.litElementHydrateSupport?.({LitElement:x});var Pr=wt.litElementPolyfillSupport;Pr?.({LitElement:x});(wt.litElementVersions??=[]).push("4.2.2");var b=n=>(a,e)=>{e!==void 0?e.addInitializer(()=>{customElements.define(n,a)}):customElements.define(n,a)};var Tr={attribute:!0,type:String,converter:Ge,reflect:!1,hasChanged:et},Rr=(n=Tr,a,e)=>{let{kind:t,metadata:r}=e,o=globalThis.litPropertyMetadata.get(r);if(o===void 0&&globalThis.litPropertyMetadata.set(r,o=new Map),t==="setter"&&((n=Object.create(n)).wrapped=!0),o.set(e.name,n),t==="accessor"){let{name:i}=e;return{set(d){let c=a.get.call(this);a.set.call(this,d),this.requestUpdate(i,c,n,!0,d)},init(d){return d!==void 0&&this.C(i,void 0,n,d),d}}}if(t==="setter"){let{name:i}=e;return function(d){let c=this[i];a.call(this,d),this.requestUpdate(i,c,n,!0,d)}}throw Error("Unsupported decorator location: "+t)};function u(n){return(a,e)=>typeof e=="object"?Rr(n,a,e):((t,r,o)=>{let i=r.hasOwnProperty(o);return r.constructor.createProperty(o,t),i?Object.getOwnPropertyDescriptor(r,o):void 0})(n,a,e)}function z(n){return u({...n,state:!0,attribute:!1})}var M={Sun:"\u2609",Moon:"\u263D",Mercury:"\u263F",Venus:"\u2640",Earth:"\u2641",Mars:"\u2642",Jupiter:"\u2643",Saturn:"\u2644",Uranus:"\u2645",Neptune:"\u2646",Pluto:"\u2647",Rahu:"\u260A",Ketu:"\u260B",Ascendant:"Asc",Lagna:"La",NorthNode:"\u260A",SouthNode:"\u260B","North node":"\u260A","South node":"\u260B",Chiron:"\u26B7",Lilith:"\u26B8","Black moon lilith":"\u26B8"},er={Sun:"Su",Moon:"Mo",Mercury:"Me",Venus:"Ve",Mars:"Ma",Jupiter:"Ju",Saturn:"Sa",Uranus:"Ur",Neptune:"Ne",Pluto:"Pl",Rahu:"Ra",Ketu:"Ke",Ascendant:"Asc",Lagna:"La"},G={Aries:"\u2648",Taurus:"\u2649",Gemini:"\u264A",Cancer:"\u264B",Leo:"\u264C",Virgo:"\u264D",Libra:"\u264E",Scorpio:"\u264F",Sagittarius:"\u2650",Capricorn:"\u2651",Aquarius:"\u2652",Pisces:"\u2653"},kt={Aries:"Ar",Taurus:"Ta",Gemini:"Ge",Cancer:"Cn",Leo:"Le",Virgo:"Vi",Libra:"Li",Scorpio:"Sc",Sagittarius:"Sg",Capricorn:"Cp",Aquarius:"Aq",Pisces:"Pi"},_=["Aries","Taurus","Gemini","Cancer","Leo","Virgo","Libra","Scorpio","Sagittarius","Capricorn","Aquarius","Pisces"],ds=_.map(n=>n.toLowerCase()),St={conjunction:"\u260C",opposition:"\u260D",trine:"\u25B3",square:"\u25A1",sextile:"\u2731",quincunx:"\u22BB",semisextile:"\u22BC"},At={heaven:"\u2630",lake:"\u2631",fire:"\u2632",thunder:"\u2633",wind:"\u2634",water:"\u2635",mountain:"\u2636",earth:"\u2637",Heaven:"\u2630",Lake:"\u2631",Fire:"\u2632",Thunder:"\u2633",Wind:"\u2634",Water:"\u2635",Mountain:"\u2636",Earth:"\u2637"},tr={"new moon":"\u{1F311}","waxing crescent":"\u{1F312}","first quarter":"\u{1F313}","waxing gibbous":"\u{1F314}","full moon":"\u{1F315}","waning gibbous":"\u{1F316}","last quarter":"\u{1F317}","waning crescent":"\u{1F318}"};var v=f`
	:host {
		display: block;
		container-type: inline-size;
		font-family: var(
			--roxy-font-sans,
			system-ui,
			-apple-system,
			BlinkMacSystemFont,
			'Segoe UI',
			Roboto,
			sans-serif
		);
		color: var(--roxy-fg, #0a0a0a);
		background: transparent;
		font-size: var(--roxy-text-base, 1rem);
		line-height: var(--roxy-leading-normal, 1.5);
		animation: roxy-fade-in var(--roxy-motion-duration, 200ms)
			var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1)) both;
	}

	*,
	*::before,
	*::after {
		box-sizing: border-box;
	}

	@keyframes roxy-fade-in {
		from {
			opacity: 0;
			transform: translateY(2px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	@media (prefers-reduced-motion: reduce) {
		:host {
			animation: none;
		}
	}

	.roxy-skeleton {
		background: linear-gradient(
			90deg,
			var(--roxy-border, #e4e4e7) 0%,
			color-mix(in srgb, var(--roxy-border, #e4e4e7) 60%, transparent) 50%,
			var(--roxy-border, #e4e4e7) 100%
		);
		background-size: 200% 100%;
		animation: roxy-shimmer 1.4s ease-in-out infinite;
		border-radius: var(--roxy-radius-md, 8px);
	}

	@keyframes roxy-shimmer {
		0% {
			background-position: 200% 0;
		}
		100% {
			background-position: -200% 0;
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.roxy-skeleton {
			animation: none;
		}
	}

	.roxy-empty {
		padding: var(--roxy-space-lg, 1.5rem);
		color: var(--roxy-muted, #71717a);
		text-align: center;
		font-size: var(--roxy-text-sm, 0.875rem);
	}

	:host(:focus-within) .roxy-card {
		outline: 2px solid var(--roxy-ring, rgba(245, 158, 11, 0.4));
		outline-offset: 2px;
	}

	/* Force the text-style variant on every Unicode glyph in the component.
	 * macOS and iOS substitute coloured emoji glyphs for the planetary and
	 * gender Unicode code points (Mars, Venus, Mercury, etc.) when the
	 * system colour-emoji font wins font selection. The text-style variant
	 * keeps glyphs monochrome so they inherit the surrounding fill colour
	 * and match the brand palette consistently across platforms.
	 *
	 * font-variant-emoji is part of CSS Fonts 4 (Safari 17+, Chrome 134+,
	 * Firefox 139+). On older browsers the rule is silently ignored.
	 */
	:host {
		font-variant-emoji: text;
	}
`;var Mr="roxy-data";function Nr(n){return n.nodeName==="SCRIPT"&&n.getAttribute("type")==="application/json"}var $=class{constructor(a){this.host=a,a.addController(this)}hostConnected(){if(this.host.data!=null)return;let a=this.read();a!==void 0&&(this.host.data=a,this.host.requestUpdate())}read(){let a=this.findInlineScript();return a?this.parse(a.textContent):void 0}findInlineScript(){for(let a of Array.from(this.host.children))if(Nr(a)&&a.classList.contains(Mr))return a;return null}parse(a){if(a?.trim())try{return JSON.parse(a)}catch{return}}};var zr={sarva:"Sarvashtakavarga",bhinna:"Bhinnashtakavarga",pinda:"Shodhya Pinda"},Ee=["sarva","bhinna","pinda"],Y=class extends x{constructor(){super();this.data=null;this.activeTab="sarva";new $(this)}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No ashtakavarga data</div>`;let e=this.data.signs??[];return s`<div class="wrap" aria-label="Ashtakavarga grid">
			<div class="head">
				<h2 class="title">Ashtakavarga</h2>
				${e.length?s`<p class="subtitle">${e.length} signs</p>`:l}
			</div>

			<div
				class="tablist"
				role="tablist"
				aria-label="Ashtakavarga views"
				@keydown=${this.onTabKeyDown}
			>
				${Ee.map(t=>s`<button
						class="tab"
						role="tab"
						id="tab-${t}"
						aria-selected=${this.activeTab===t?"true":"false"}
						aria-controls="panel-${t}"
						tabindex=${this.activeTab===t?"0":"-1"}
						@click=${()=>{this.activeTab=t}}
					>
						${zr[t]}
					</button>`)}
			</div>

			<div
				id="panel-${this.activeTab}"
				role="tabpanel"
				aria-labelledby="tab-${this.activeTab}"
			>
				${this.activeTab==="sarva"?this.renderSarva(e):this.activeTab==="bhinna"?this.renderBhinna(e):this.renderPinda()}
			</div>
		</div>`}onTabKeyDown(e){let t=Ee.indexOf(this.activeTab);e.key==="ArrowRight"?(e.preventDefault(),this.activeTab=Ee[(t+1)%Ee.length],this.focusActiveTab()):e.key==="ArrowLeft"&&(e.preventDefault(),this.activeTab=Ee[(t-1+Ee.length)%Ee.length],this.focusActiveTab())}focusActiveTab(){requestAnimationFrame(()=>{this.shadowRoot?.querySelector(`#tab-${this.activeTab}`)?.focus()})}bhinnaHeat(e){return e<=1?"heat-1":e<=2?"heat-2":e<=3?"heat-3":e<=4?"heat-4":e<=5?"heat-5":e<=6?"heat-6":"heat-7"}sarvaHeat(e){return e<=18?"heat-1":e<=23?"heat-2":e<=28?"heat-3":e<=32?"heat-4":e<=37?"heat-5":e<=42?"heat-6":"heat-7"}renderSarva(e){let t=this.data.sarvashtakavarga;return t?s`<div class="overflow-scroll">
			<table aria-label="Sarvashtakavarga bindu counts per sign">
				<thead>
					<tr>
						<th scope="col">Sign</th>
						<th scope="col">Bindus</th>
					</tr>
				</thead>
				<tbody>
					${e.map((r,o)=>{let i=t.bindus[o]??0,d=this.sarvaHeat(i);return s`<tr>
							<td>
								<div class="planet-cell">
									<span class="glyph" aria-hidden="true">${G[r]??""}</span>
									${r}
								</div>
							</td>
							<td class="${`heat-cell ${d}`}">${i}</td>
						</tr>`})}
				</tbody>
				<tfoot>
					<tr class="total-row">
						<td>Total</td>
						<td>${t.total}</td>
					</tr>
				</tfoot>
			</table>
		</div>`:s`<p class="roxy-empty">No sarvashtakavarga data</p>`}renderBhinna(e){let t=this.data.bhinnashtakavarga;return t?.length?s`<div class="overflow-scroll">
			<table class="bhinna-table" aria-label="Bhinnashtakavarga planet-by-sign grid">
				<thead>
					<tr>
						<th scope="col">Planet</th>
						${e.map(r=>s`<th scope="col" title=${r}>${G[r]??r.slice(0,2)}</th>`)}
						<th scope="col">Total</th>
					</tr>
				</thead>
				<tbody>
					${t.map(r=>s`<tr>
						<td>${r.planet}</td>
						${r.bindus.map(o=>{let i=this.bhinnaHeat(o);return s`<td class="${`heat-cell ${i}`}">${o}</td>`})}
						<td>${r.total}</td>
					</tr>`)}
				</tbody>
			</table>
		</div>`:s`<p class="roxy-empty">No bhinnashtakavarga data</p>`}renderPinda(){let e=this.data.shodhyaPinda;return e?.length?s`<div class="overflow-scroll">
			<table aria-label="Shodhya Pinda planet strength scores">
				<thead>
					<tr>
						<th scope="col">Planet</th>
						<th scope="col">Rashi Pinda</th>
						<th scope="col">Graha Pinda</th>
						<th scope="col">Shodhya Pinda</th>
					</tr>
				</thead>
				<tbody>
					${e.map(t=>s`<tr>
							<td>${t.planet}</td>
							<td>${t.rashiPinda}</td>
							<td>${t.grahaPinda}</td>
							<td>${t.shodhyaPinda}</td>
						</tr>`)}
				</tbody>
			</table>
		</div>`:s`<p class="roxy-empty">No shodhya pinda data</p>`}};Y.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.head {
				display: flex;
				justify-content: space-between;
				align-items: baseline;
				gap: var(--roxy-space-md, 1rem);
				flex-wrap: wrap;
			}

			.title {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
			}

			.subtitle {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
				margin: 0;
			}

			/* Tabs */
			.tablist {
				display: flex;
				gap: 2px;
				border-bottom: 2px solid var(--roxy-border, #e4e4e7);
			}

			.tab {
				padding: var(--roxy-space-xs, 0.25rem) var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-sm, 0.875rem);
				background: none;
				border: none;
				border-bottom: 2px solid transparent;
				margin-bottom: -2px;
				cursor: pointer;
				color: var(--roxy-muted, #71717a);
				font-family: inherit;
				transition: color var(--roxy-motion-duration, 200ms) var(--roxy-motion-easing, ease);
			}

			.tab[aria-selected='true'] {
				color: var(--roxy-accent-fg, #b45309);
				border-bottom-color: var(--roxy-accent, #f59e0b);
				font-weight: var(--roxy-weight-bold, 600);
			}

			.tab:hover:not([aria-selected='true']) {
				color: var(--roxy-fg, #0a0a0a);
			}

			/* Tables */
			.overflow-scroll {
				overflow-x: auto;
				-webkit-overflow-scrolling: touch;
			}

			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
			}

			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				text-align: center;
			}

			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.06em;
			}

			td:first-child,
			th:first-child {
				text-align: left;
			}

			.glyph {
				font-size: 1.1em;
				margin-right: 3px;
				line-height: 1;
			}

			.planet-cell {
				display: flex;
				align-items: center;
				gap: 4px;
				white-space: nowrap;
			}

			.total-row td {
				font-weight: var(--roxy-weight-bold, 600);
				border-top: 2px solid var(--roxy-border, #e4e4e7);
				border-bottom: none;
			}

			/* Heat cells. Single base hue (var --roxy-heat) mixed with
			 * transparent at increasing percentages produces seven readable
			 * tiers in both light and dark themes. Text colour stays
			 * var(--roxy-fg) so it inverts with the host theme without
			 * per-tier overrides. */
			.heat-cell {
				border-radius: var(--roxy-radius-sm, 4px);
				font-weight: var(--roxy-weight-bold, 600);
				min-width: 2rem;
				font-variant-numeric: tabular-nums;
				color: var(--roxy-fg, currentColor);
			}

			.heat-1 { background: color-mix(in srgb, var(--roxy-heat, #ef4444) 6%, transparent); }
			.heat-2 { background: color-mix(in srgb, var(--roxy-heat, #ef4444) 14%, transparent); }
			.heat-3 { background: color-mix(in srgb, var(--roxy-heat, #ef4444) 26%, transparent); }
			.heat-4 { background: color-mix(in srgb, var(--roxy-heat, #ef4444) 40%, transparent); }
			.heat-5 { background: color-mix(in srgb, var(--roxy-heat, #ef4444) 55%, transparent); }
			.heat-6 { background: color-mix(in srgb, var(--roxy-heat, #ef4444) 72%, transparent); }
			.heat-7 { background: color-mix(in srgb, var(--roxy-heat, #ef4444) 90%, transparent); }

			/* Bhinna grid: planet header column narrower */
			.bhinna-table th:first-child,
			.bhinna-table td:first-child {
				min-width: 5rem;
			}

			/* Tight cells below 480px so the 14-column bhinna grid stops
			 * overflowing the viewport. The wrapper keeps overflow-x:auto as
			 * a fallback for very long content. */
			@container (max-width: 480px) {
				.bhinna-table th,
				.bhinna-table td {
					padding: 0.3rem 0.35rem;
					font-size: var(--roxy-text-xs, 0.75rem);
				}
				.bhinna-table th:first-child,
				.bhinna-table td:first-child {
					min-width: 3.5rem;
				}
				.heat-cell {
					min-width: 1.5rem;
				}
			}
			/* Visual cue that the bhinna table is scrollable below the breakpoint:
			 * a soft gradient at the right edge so users see there is more to scroll. */
			.overflow-scroll {
				mask-image: linear-gradient(
					to right,
					transparent 0,
					black 0.5rem,
					black calc(100% - 1rem),
					transparent 100%
				);
				-webkit-mask-image: linear-gradient(
					to right,
					transparent 0,
					black 0.5rem,
					black calc(100% - 1rem),
					transparent 100%
				);
			}
		`],p([u({attribute:!1})],Y.prototype,"data",2),p([z()],Y.prototype,"activeTab",2),Y=p([b("roxy-ashtakavarga-grid")],Y);var rr={physical:"#dc2626",emotional:"#0284c7",intellectual:"#16a34a",intuitive:"#a855f7",aesthetic:"#f59e0b",awareness:"#ec4899",spiritual:"#14b8a6",passion:"#ef4444",mastery:"#6366f1",wisdom:"#475569"},U=class extends x{constructor(){super();this.data=null;this.mode="daily";new $(this)}render(){let e=this.data;return e?this.mode==="critical-days"&&"criticalDays"in e?this.renderCritical(e):this.mode==="forecast"&&"days"in e?this.renderForecast(e):this.renderDaily(e):s`<div class="roxy-empty" role="status">No biorhythm data</div>`}renderDaily(e){let t=e.quickRead??{},r=Object.entries(t).map(([o,i])=>{let d=typeof i=="number"?i:0,c=Math.abs(d)>1?d/100:d;return[o,c]});return s`<section class="wrap" aria-label="Daily biorhythm">
			<header class="head">
				<h2 class="title">Biorhythm</h2>
				${typeof e.energyRating=="number"?s`<span class="energy">Energy ${e.energyRating}/10</span>`:l}
			</header>
			<div class="bars" role="list">
				${r.map(([o,i])=>{let d=(i+1)/2*100,c=rr[o]??"var(--roxy-accent, #f59e0b)";return s`<div class="bar" role="listitem">
						<span style="text-transform: capitalize">${o}</span>
						<span class="track">
							<span
								class="fill"
								style="width: ${d}%; background: ${c}"
							></span>
						</span>
						<span class="value">${Math.round(i*100)}%</span>
					</div>`})}
			</div>
			${e.dailyMessage?s`<p class="advice">${e.dailyMessage}</p>`:l}
			${e.advice?s`<p class="advice">${e.advice}</p>`:l}
		</section>`}renderForecast(e){let t=e.days??[];if(t.length===0)return s`<div class="roxy-empty" role="status">No forecast</div>`;let r=600,o=160,i=r/Math.max(t.length-1,1),d=["physical","emotional","intellectual","intuitive"];return s`<section class="wrap" aria-label="Biorhythm forecast">
			<header class="head">
				<h2 class="title">Forecast</h2>
				<span class="energy">${e.startDate} - ${e.endDate}</span>
			</header>
			<svg
				viewBox="0 0 ${r} ${o}"
				role="img"
				aria-label="Biorhythm cycle lines across the forecast window"
			>
				<title>Biorhythm forecast</title>
				<line
					x1="0"
					y1=${o/2}
					x2=${r}
					y2=${o/2}
					stroke="var(--roxy-border, #e4e4e7)"
					stroke-width="1"
				/>
				${d.map(c=>{let m=t.map((h,y)=>{let S=h[c]??0,w=y*i,we=o/2-S/100*(o/2-8);return`${w.toFixed(2)},${we.toFixed(2)}`}).join(" "),g=rr[c]??"#475569";return k`<polyline points=${m} fill="none" stroke=${g} stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />`})}
			</svg>
			${e.summary?.periodAdvice?s`<p class="advice">${e.summary.periodAdvice}</p>`:l}
		</section>`}renderCritical(e){return s`<section class="wrap" aria-label="Critical days">
			<header class="head">
				<h2 class="title">Critical days</h2>
				<span class="energy">${e.totalCriticalDays} total</span>
			</header>
			<div>
				${e.criticalDays.map(t=>s`<span class="crit"
						>${t.date} · ${t.cycle} ${t.severity}</span
					>`)}
			</div>
		</section>`}};U.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}
			.head {
				display: flex;
				justify-content: space-between;
				align-items: center;
				flex-wrap: wrap;
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.energy {
				font-variant-numeric: tabular-nums;
				color: var(--roxy-accent-fg, #b45309);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.bars {
				display: grid;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.bar {
				display: grid;
				grid-template-columns: 8rem 1fr 3.5rem;
				gap: var(--roxy-space-sm, 0.5rem);
				align-items: center;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.track {
				height: 14px;
				background: var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-full, 9999px);
				overflow: hidden;
				position: relative;
			}
			.fill {
				display: block;
				height: 100%;
				transition:
					width var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}
			.value {
				font-variant-numeric: tabular-nums;
				text-align: right;
				color: var(--roxy-muted, #71717a);
			}
			.advice {
				color: var(--roxy-fg, #0a0a0a);
			}
			.alert {
				background: color-mix(in srgb, var(--roxy-warning, #ea580c) 12%, transparent);
				border: 1px solid color-mix(in srgb, var(--roxy-warning, #ea580c) 32%, transparent);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-sm, 0.5rem);
				font-size: var(--roxy-text-sm, 0.875rem);
				margin: 0;
			}
			svg {
				display: block;
				width: 100%;
				height: auto;
			}
			.crit {
				background: color-mix(in srgb, var(--roxy-danger, #dc2626) 12%, transparent);
				border-radius: var(--roxy-radius-sm, 4px);
				padding: 4px 8px;
				font-size: var(--roxy-text-xs, 0.75rem);
				display: inline-block;
				margin: 2px;
			}
		`],p([u({attribute:!1})],U.prototype,"data",2),p([u({type:String,reflect:!0})],U.prototype,"mode",2),U=p([b("roxy-biorhythm-chart")],U);function E(n){return n?n.charAt(0).toUpperCase()+n.slice(1).toLowerCase():""}function pe(n){return n.replace(/[_-]+/g," ").replace(/([a-z])([A-Z])/g,"$1 $2").replace(/^\w/,a=>a.toUpperCase())}function ar(n){try{let a=new Date(n);return Number.isNaN(a.getTime())?n:a.toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"})}catch{return n}}var me=class extends x{constructor(){super();this.data=null;new $(this)}isCurrent(e){let t=Date.now(),r=Date.parse(e.start),o=Date.parse(e.end);return Number.isNaN(r)||Number.isNaN(o)?!1:t>=r&&t<o}renderTile(e){let t=e.effect==="Good"?"good":e.effect==="Bad"?"bad":"neutral",r=this.isCurrent(e),o=M[E(e.lord)]??"",i=`${ar(e.start)} - ${ar(e.end)}`;return s`<div
			class="cho-tile ${t}${r?" now":""}"
			role="listitem"
			aria-current=${r?"time":"false"}
		>
			<span class="tile-name">
				${e.name}${r?s`<span class="now-badge">Now</span>`:l}
			</span>
			<span class="tile-time" aria-label="Time range">${i}</span>
			<span class="tile-lord">
				${o?s`<span aria-hidden="true">${o}</span>`:l}
				${e.lord}
			</span>
		</div>`}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No choghadiya data</div>`;let{date:e,dayChoghadiya:t,nightChoghadiya:r}=this.data;return s`<div class="wrap">
			<div class="header">
				<h2 class="title">Choghadiya</h2>
				${e?s`<p class="subtitle">${e}</p>`:l}
			</div>

			<div class="cho-grid">
				<section class="period-col" aria-label="Day muhurta periods">
					<h3 class="period-heading">Day</h3>
					<div role="list" aria-label="Daytime choghadiya">
						${t&&t.length>0?t.map(o=>this.renderTile(o)):s`<p class="roxy-empty" role="status">No daytime periods</p>`}
					</div>
				</section>

				<section class="period-col" aria-label="Night muhurta periods">
					<h3 class="period-heading">Night</h3>
					<div role="list" aria-label="Nighttime choghadiya">
						${r&&r.length>0?r.map(o=>this.renderTile(o)):s`<p class="roxy-empty" role="status">No nighttime periods</p>`}
					</div>
				</section>
			</div>
		</div>`}};me.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}
			.header {
				display: grid;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.title {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
			}
			.subtitle {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
				margin: 0;
			}
			.cho-grid {
				display: grid;
				grid-template-columns: 1fr;
				gap: var(--roxy-space-md, 1rem);
			}
			@media (min-width: 720px) {
				.cho-grid {
					grid-template-columns: 1fr 1fr;
				}
			}
			.period-col {
				display: grid;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.period-heading {
				font-size: var(--roxy-text-base, 1rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0 0 var(--roxy-space-xs, 0.25rem);
				color: var(--roxy-fg, #0a0a0a);
			}
			.cho-tile {
				display: grid;
				grid-template-columns: 1fr auto;
				align-items: center;
				gap: 0.25em 0.75em;
				padding: 0.55em 0.85em;
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
			}
			.cho-tile.good {
				background: color-mix(in srgb, var(--roxy-success, #22c55e) 18%, transparent);
				border-color: color-mix(in srgb, var(--roxy-success, #22c55e) 45%, transparent);
				color: var(--roxy-fg, #0a0a0a);
			}
			.cho-tile.bad {
				background: color-mix(in srgb, var(--roxy-danger, #ef4444) 18%, transparent);
				border-color: color-mix(in srgb, var(--roxy-danger, #ef4444) 45%, transparent);
				color: var(--roxy-fg, #0a0a0a);
			}
			.cho-tile.neutral {
				background: transparent;
				color: var(--roxy-fg, #0a0a0a);
			}
			.cho-tile.now {
				outline: 2px solid var(--roxy-accent, #f59e0b);
				outline-offset: 1px;
				box-shadow: 0 0 0 4px
					color-mix(in srgb, var(--roxy-accent, #f59e0b) 18%, transparent);
			}
			.now-badge {
				display: inline-block;
				margin-left: 0.4em;
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-accent-fg, #b45309);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.tile-name {
				font-size: var(--roxy-text-base, 1rem);
				font-weight: var(--roxy-weight-bold, 600);
				grid-column: 1;
			}
			.tile-time {
				font-size: var(--roxy-text-xs, 0.75rem);
				opacity: 0.8;
				white-space: nowrap;
				grid-column: 2;
				grid-row: 1 / 3;
				text-align: right;
				align-self: center;
			}
			.tile-lord {
				font-size: var(--roxy-text-sm, 0.875rem);
				opacity: 0.85;
				grid-column: 1;
				display: flex;
				align-items: center;
				gap: 0.25em;
			}
		`],p([u({attribute:!1})],me.prototype,"data",2),me=p([b("roxy-choghadiya-grid")],me);function I(n){if(typeof n!="string"||n.length===0||/^\d{4}-\d{2}-\d{2}$/.test(n))return"";let e=/^\d{2}:\d{2}(:\d{2})?$/.test(n)?`1970-01-01T${n}`:n,t=new Date(e);return Number.isNaN(t.getTime())?n:t.toLocaleTimeString(void 0,{hour:"numeric",minute:"2-digit",hour12:!0})}function at(n){if(typeof n!="string"||n.length===0)return"";let a=new Date(/^\d{4}-\d{2}-\d{2}$/.test(n)?`${n}T00:00:00`:n);return Number.isNaN(a.getTime())?n:a.toLocaleDateString(void 0,{month:"short",day:"numeric",year:"numeric"})}function Ct(n){if(!n)return"";let a=I(n.start),e=I(n.end);return a&&e?`${a} - ${e}`:a||e||""}function C(n,a=1){return typeof n!="number"||!Number.isFinite(n)?"":n.toFixed(a).replace(/\.?0+$/,"")}function sr(n,a=1){let e=C(n,a);return e?`${e}%`:""}var Ve={conjunction:"aspect-conjunction",sextile:"aspect-sextile",square:"aspect-square",trine:"aspect-trine",opposition:"aspect-opposition"};function Me(n){return(n.type??"").toLowerCase().replace(/_/g,"-")}var F=class extends x{constructor(){super();this.data=null;this.mode="astrology";new $(this)}getBreakdown(){let e=this.data;if(!e)return{};if("categories"in e&&e.categories){let t={};for(let[r,o]of Object.entries(e.categories))typeof o=="number"&&Number.isFinite(o)&&(t[r]=o);return t}return{}}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No compatibility data</div>`;let t=e.overallScore,r=this.getBreakdown(),o="rating"in e?e.rating:void 0,i="archetype"in e?e.archetype:void 0,d="advice"in e?e.advice:void 0,c="summary"in e?e.summary:void 0,m="interpretation"in e?e.interpretation:void 0,g="strengths"in e?e.strengths:void 0,h="challenges"in e?e.challenges:void 0,y="keyAspects"in e?e.keyAspects:void 0;return s`<article
			class="card"
			aria-label=${`Compatibility (${this.mode})`}
		>
			<div class="head">
				<h2>${this.mode} compatibility</h2>
				<div>
					${typeof t=="number"?s`<div class="score">${C(t,0)}</div>`:l}
					${o?s`<div class="rating">${o}</div>`:l}
				</div>
			</div>

			${Object.keys(r).length>0?s`<div role="list">
						${Object.entries(r).map(([S,w])=>s`<div class="bar-row" role="listitem">
								<span style="text-transform: capitalize">${S}</span>
								<span class="bar"
									><span style="width: ${Math.max(0,Math.min(100,w))}%"></span
								></span>
								<span>${C(w,0)}</span>
							</div>`)}
					</div>`:l}
			${i?s`<p>
						<span class="archetype">${i.label}</span>
						${i.description?s` · ${i.description}`:l}
					</p>`:l}
			${c?s`<p>${c}</p>`:l}
			${m&&!c?s`<p>${m}</p>`:l}
			${d?s`<p>${d}</p>`:l}
			${(g?.length??0)>0||(h?.length??0)>0?s`<div class="lists">
						${g?.length?s`<div>
									<h3>Strengths</h3>
									<ul>
										${g.map(S=>s`<li>${S}</li>`)}
									</ul>
								</div>`:l}
						${h?.length?s`<div>
									<h3>Challenges</h3>
									<ul>
										${h.map(S=>s`<li>${S}</li>`)}
									</ul>
								</div>`:l}
					</div>`:l}
			${y?.length?s`<div>
						<h3 style="margin: 0 0 0.25rem; font-size: var(--roxy-text-xs); color: var(--roxy-muted); text-transform: uppercase; letter-spacing: 0.06em;">Key aspects</h3>
						<ul style="margin: 0; padding-left: 1rem; font-size: var(--roxy-text-sm);">
							${y.slice(0,6).map(S=>s`<li>${_r(S)}</li>`)}
						</ul>
					</div>`:l}
		</article>`}};F.styles=[v,f`
			.card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.head {
				display: grid;
				grid-template-columns: 1fr auto;
				align-items: center;
				gap: var(--roxy-space-md, 1rem);
			}
			.head h2 {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: capitalize;
			}

			.score {
				font-variant-numeric: tabular-nums;
				font-size: 2rem;
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-accent-fg, #b45309);
				line-height: 1;
			}
			.rating {
				color: var(--roxy-secondary, #475569);
				font-size: var(--roxy-text-sm, 0.875rem);
			}

			.bar-row {
				display: grid;
				grid-template-columns: 8rem 1fr 3.5rem;
				gap: var(--roxy-space-sm, 0.5rem);
				align-items: center;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.bar {
				height: 8px;
				background: var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-full, 9999px);
				overflow: hidden;
			}
			.bar > span {
				display: block;
				height: 100%;
				background: var(--roxy-accent, #f59e0b);
				transition:
					width var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}
			.bar-row > span:last-child {
				font-variant-numeric: tabular-nums;
				color: var(--roxy-muted, #71717a);
				text-align: right;
			}

			.archetype {
				color: var(--roxy-accent-fg, #b45309);
				font-weight: var(--roxy-weight-bold, 600);
			}

			.lists {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
				gap: var(--roxy-space-md, 1rem);
			}
			.lists h3 {
				margin: 0 0 var(--roxy-space-xs, 0.25rem);
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.lists ul {
				margin: 0;
				padding-left: var(--roxy-space-md, 1rem);
			}
		`],p([u({attribute:!1})],F.prototype,"data",2),p([u({type:String,reflect:!0})],F.prototype,"mode",2),F=p([b("roxy-compatibility-card")],F);function _r(n){let a=n.type.toLowerCase().replace(/_/g,"-"),e=typeof n.orb=="number"?` (orb ${C(n.orb,1)}\xB0)`:"",t=[n.planet1,a,n.planet2].filter(Boolean).join(" ");return n.description?`${t}${e} \xB7 ${n.description}`:`${t}${e}`}var W=class extends x{constructor(){super();this.data=null;this.period="current";new $(this)}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No dasha data</div>`;let t=this.collectPeriods(e),r=t.length?Math.max(...t.map(o=>o.durationYears)):0;return s`<div class="wrap" aria-label="Dasha timeline">
			<header class="head">
				<h2 class="title">
					${this.period==="major"?"Vimshottari Mahadasha":this.period==="sub"?"Antardasha":"Active dashas"}
				</h2>
				${"nakshatraName"in e&&e.nakshatraName?s`<div class="nakshatra">
						Moon nakshatra: ${e.nakshatraName}
						${"nakshatraLord"in e&&e.nakshatraLord?s`(lord ${e.nakshatraLord})`:l}
					</div>`:l}
			</header>

			${this.renderBirthBalance(e)}
			${this.period==="current"?this.renderCurrent(e):l}
			${t.length>0?s`<div class="timeline" role="list">
						${t.map(o=>this.renderBar(o,r))}
					</div>`:l}
			${this.renderActiveInterpretation(t)}
		</div>`}renderBirthBalance(e){if(!("birthDashaBalance"in e)||!e.birthDashaBalance)return l;let t=e.birthDashaBalance,r="nakshatraLord"in e&&e.nakshatraLord?e.nakshatraLord:"",o=t.years??0,i=t.months??0,d=t.days??0,c=[];o&&c.push(`${o}y`),i&&c.push(`${i}m`),d&&c.push(`${d}d`);let m=c.length?c.join(" "):"0d";return s`<p class="balance">
			Birth dasha balance: ${m} of
			${r?s`<strong>${r}</strong>`:"the opening mahadasha"} remained at birth.
		</p>`}renderActiveInterpretation(e){let t=e.find(r=>this.isCurrent(r));return t?.interpretation?s`<details class="interp">
			<summary>${t.planet} mahadasha interpretation</summary>
			<p>${t.interpretation}</p>
		</details>`:l}renderCurrent(e){return"mahadasha"in e?s`<div class="current">
			${"mahadasha"in e&&e.mahadasha?s`<div>
					<span>Mahadasha</span>
					<strong>${e.mahadasha.planet}</strong>
					${"remainingInMahadasha"in e&&e.remainingInMahadasha?s`<small>${C(e.remainingInMahadasha.years+e.remainingInMahadasha.months/12,1)} years left</small>`:l}
				</div>`:l}
			${"antardasha"in e&&e.antardasha?s`<div>
					<span>Antardasha</span>
					<strong>${e.antardasha.planet}</strong>
					${"remainingInAntardasha"in e&&e.remainingInAntardasha?s`<small>${C(e.remainingInAntardasha.years+e.remainingInAntardasha.months/12,1)} years left</small>`:l}
				</div>`:l}
			${"pratyantardasha"in e&&e.pratyantardasha?s`<div>
					<span>Pratyantardasha</span>
					<strong>${e.pratyantardasha.planet}</strong>
					${"remainingInPratyantardasha"in e&&e.remainingInPratyantardasha?s`<small>${C(e.remainingInPratyantardasha.years+e.remainingInPratyantardasha.months/12,1)} years left</small>`:l}
				</div>`:l}
		</div>`:l}collectPeriods(e){return"mahadashas"in e&&e.mahadashas?.length?e.mahadashas:"antardashas"in e&&e.antardashas?.length?e.antardashas:[]}isCurrent(e){if(!e.startDate||!e.endDate)return!1;let t=Date.now(),r=Date.parse(e.startDate),o=Date.parse(e.endDate);return Number.isNaN(r)||Number.isNaN(o)?!1:t>=r&&t<o}progressIn(e){if(!e.startDate||!e.endDate)return-1;let t=Date.parse(e.startDate),r=Date.parse(e.endDate),o=Date.now();return Number.isNaN(t)||Number.isNaN(r)||o<t||o>=r||r<=t?-1:(o-t)/(r-t)}renderBar(e,t){let r=e.durationYears,o=t>0?r/t*100:0,i=this.isCurrent(e),d=i?this.progressIn(e):-1,c=i?"bar-track bar-now":"bar-track";return s`<div
			class=${i?"bar now":"bar"}
			role="listitem"
			aria-current=${i?"time":"false"}
		>
			<span>
				<strong>${e.planet}</strong>${i?s`<span class="now-badge">Now</span>`:l}
			</span>
			<span class=${c}>
				<span class="bar-fill" style="width: ${o}%"></span>
				${d>=0?s`<span
							class="bar-progress"
							style="left: ${d*o}%"
							aria-hidden="true"
						></span>`:l}
			</span>
			<span class="dates">
				${e.startDate?or(e.startDate):""}
				${e.endDate?s`- ${or(e.endDate)}`:""}
			</span>
		</div>`}};W.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}
			.head {
				display: flex;
				justify-content: space-between;
				align-items: center;
				flex-wrap: wrap;
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.nakshatra {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}

			.current {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
				gap: var(--roxy-space-md, 1rem);
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-md, 1rem);
				box-shadow: var(--roxy-shadow-sm);
			}
			.current div span:first-child {
				display: block;
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-xs, 0.75rem);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.current div strong {
				font-size: var(--roxy-text-base, 1rem);
				color: var(--roxy-fg, #0a0a0a);
			}

			.balance {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
				border-left: 2px solid var(--roxy-border, #e4e4e7);
				padding-left: var(--roxy-space-sm, 0.5rem);
				margin: 0;
			}
			.timeline {
				display: grid;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.bar {
				display: grid;
				grid-template-columns: 5rem 1fr 8rem;
				gap: var(--roxy-space-sm, 0.5rem);
				align-items: center;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.bar.now strong {
				color: var(--roxy-accent-fg, #b45309);
			}
			.now-badge {
				display: inline-block;
				margin-left: 0.4em;
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-accent-fg, #b45309);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.bar-track {
				position: relative;
				height: 14px;
				background: var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-full, 9999px);
				overflow: hidden;
			}
			.bar-fill {
				display: block;
				height: 100%;
				background: var(--roxy-accent, #f59e0b);
				opacity: 0.45;
				transition:
					width var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}
			.bar-now .bar-fill {
				opacity: 1;
			}
			.bar-progress {
				position: absolute;
				top: -2px;
				bottom: -2px;
				width: 2px;
				background: var(--roxy-accent-fg, #b45309);
				border-radius: 2px;
				box-shadow: 0 0 0 2px
					color-mix(in srgb, var(--roxy-accent, #f59e0b) 35%, transparent);
			}
			.dates {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-xs, 0.75rem);
				font-variant-numeric: tabular-nums;
				text-align: right;
			}
			details.interp {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				background: var(--roxy-bg, #fff);
			}
			details.interp summary {
				cursor: pointer;
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			details.interp p {
				margin: var(--roxy-space-sm, 0.5rem) 0 0;
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
			}
		`],p([u({attribute:!1})],W.prototype,"data",2),p([u({type:String,reflect:!0})],W.prototype,"period",2),W=p([b("roxy-dasha-timeline")],W);function or(n){let a=n.match(/^(\d{4})/);return a?a[1]:n}var Dr=["title","name","label","heading","overview","summary"],Or=["imageUrl","image","icon","symbol"],Hr=["imageUrl","image"],Gr=6,J=class extends x{constructor(){super();this.data=null;this.depth=0;new $(this)}render(){return this.data==null?s`<div class="roxy-empty" role="status">No data</div>`:this.depth>=Gr?s`<div class="roxy-empty" role="status">…</div>`:s`<div
			class="roxy-card"
			aria-label="Generic data display"
		>
			${this.renderValue(this.data)}
		</div>`}renderValue(e){return e==null?l:typeof e=="string"?s`<p>${e}</p>`:typeof e=="number"||typeof e=="boolean"?s`<p>${String(e)}</p>`:Array.isArray(e)?this.renderArray(e):this.renderObject(e)}renderArray(e){return e.length===0?s`<div class="roxy-empty" role="status">Empty list</div>`:e.every(o=>o===null||["string","number","boolean"].includes(typeof o))?s`<ul class="roxy-chips">
				${e.map(o=>s`<li>${String(o)}</li>`)}
			</ul>`:e.every(o=>o!==null&&typeof o=="object"&&!Array.isArray(o))?this.renderTable(e):s`<ol>
			${e.map(o=>s`<li>${this.renderValue(o)}</li>`)}
		</ol>`}renderTable(e){let t=this.collectKeys(e);return s`<table class="roxy-table" role="table">
			<thead>
				<tr>
					${t.map(r=>s`<th>${pe(r)}</th>`)}
				</tr>
			</thead>
			<tbody>
				${e.map(r=>s`<tr>
						${t.map(o=>s`<td>${this.formatPrimitive(r[o])}</td>`)}
					</tr>`)}
			</tbody>
		</table>`}renderObject(e){let t=Dr.find(d=>typeof e[d]=="string"),r=Or.find(d=>typeof e[d]=="string"&&e[d].startsWith("http")),o=t!=="summary"&&typeof e.summary=="string"?"summary":null,i=Object.entries(e).filter(([d,c])=>d!==t&&d!==o&&!Hr.includes(d)&&c!==null&&c!==void 0);return s`
			${r?s`<img
						class="roxy-image"
						src=${String(e[r])}
						alt=${t?String(e[t]):"illustration"}
						loading="lazy"
					/>`:l}
			${t?s`<h3 class="roxy-title">${e[t]}</h3>`:l}
			${o?s`<p class="roxy-summary">${e[o]}</p>`:l}
			${i.length>0?s`<dl class="roxy-rows">
						${i.map(([d,c])=>s`
								<dt>${pe(d)}</dt>
								<dd>${this.renderField(c)}</dd>
							`)}
					</dl>`:l}
		`}renderField(e){return e==null?"":typeof e=="string"?e:typeof e=="number"||typeof e=="boolean"?String(e):Array.isArray(e)&&e.every(r=>["string","number","boolean"].includes(typeof r))?s`<ul class="roxy-chips">
					${e.map(r=>s`<li>${String(r)}</li>`)}
				</ul>`:s`<roxy-data .data=${e} .depth=${this.depth+1}></roxy-data>`}formatPrimitive(e){return e==null?"":typeof e=="string"?e:typeof e=="number"||typeof e=="boolean"?String(e):Array.isArray(e)?e.map(String).join(", "):JSON.stringify(e)}collectKeys(e){let t=new Set;for(let r of e)for(let o of Object.keys(r))t.add(o);return Array.from(t)}};J.styles=[v,f`
			.roxy-card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-md, 1rem);
				box-shadow: var(--roxy-shadow-sm);
			}

			.roxy-title {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0 0 var(--roxy-space-sm, 0.5rem) 0;
				color: var(--roxy-primary, #0f172a);
				letter-spacing: var(--roxy-tracking-tight);
			}

			.roxy-summary {
				color: var(--roxy-secondary, #475569);
				margin: 0 0 var(--roxy-space-md, 1rem) 0;
				font-size: var(--roxy-text-sm, 0.875rem);
			}

			dl.roxy-rows {
				margin: 0;
				display: grid;
				grid-template-columns: minmax(8ch, max-content) 1fr;
				gap: var(--roxy-space-xs, 0.25rem) var(--roxy-space-md, 1rem);
			}
			dl.roxy-rows dt {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
				text-transform: capitalize;
			}
			dl.roxy-rows dd {
				margin: 0;
				color: var(--roxy-fg, #0a0a0a);
				font-size: var(--roxy-text-sm, 0.875rem);
				word-break: break-word;
			}

			ul.roxy-chips {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
				padding: 0;
				margin: 0;
				list-style: none;
			}
			ul.roxy-chips li {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 14%, transparent);
				color: var(--roxy-fg, #0a0a0a);
				padding: 2px 8px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
			}

			table.roxy-table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			table.roxy-table th,
			table.roxy-table td {
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				padding: var(--roxy-space-sm, 0.5rem);
				text-align: left;
				text-transform: none;
			}
			table.roxy-table th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: capitalize;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.04em;
			}

			.roxy-image {
				max-width: 100%;
				height: auto;
				border-radius: var(--roxy-radius-md, 8px);
				margin-bottom: var(--roxy-space-md, 1rem);
			}

			.roxy-section {
				margin-bottom: var(--roxy-space-md, 1rem);
			}
			.roxy-section h4 {
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-secondary, #475569);
				margin: 0 0 var(--roxy-space-xs, 0.25rem) 0;
				text-transform: capitalize;
			}
		`],p([u({attribute:!1})],J.prototype,"data",2),p([u({attribute:!1})],J.prototype,"depth",2),J=p([b("roxy-data")],J);function B(n){let a=n%360;return a<0?a+360:a}function K(n){let a=B(n),e=Math.floor(a/30)%12,t=a%30,r=Math.floor(t),o=(t-r)*60,i=Math.floor(o),d=Math.round((o-i)*60);return{sign:_[e]??"Aries",signIndex:e,degree:r,minute:i,second:d}}function st(n){let{sign:a,degree:e,minute:t}=K(n);return`${e}\xB0 ${a} ${String(t).padStart(2,"0")}'`}function Et(n){return B(n+180)}function nr(n,a){let e=B(n),t=B(a)-e;return t<0&&(t+=360),B(e+t/2)}function P(n,a,e,t){let r=t*Math.PI/180;return{x:n+e*Math.cos(r),y:a+e*Math.sin(r)}}var j=400,T=20,O=j-2*T,N=j/2,jr=Object.fromEntries(_.map(n=>[n.toLowerCase(),n])),Le=[{id:"north",label:"North"},{id:"south",label:"South"},{id:"east",label:"East"}],Ir="\u02B3";function ir(n,a){return typeof n.longitude!="number"||!Number.isFinite(n.longitude)?!1:K(n.longitude).sign.toLowerCase()!==a.toLowerCase()}function Br(n,a){let e=er[E(n.graha)]??n.graha.slice(0,2),t=n.isRetrograde?Ir:"";if(typeof n.longitude!="number"||!Number.isFinite(n.longitude)||ir(n,a))return`${e}${t}`;let{degree:r}=K(n.longitude);return`${e} ${r}\xB0${t}`}function Kr(n,a){let e=[E(n.graha)],t=ir(n,a);if(t&&e.push(`in ${a}`),typeof n.longitude=="number"&&Number.isFinite(n.longitude)){let r=K(n.longitude),o=String(r.minute).padStart(2,"0");e.push(t?`D1: ${r.degree}\xB0${o}' ${r.sign}`:`${r.degree}\xB0${o}' ${r.sign}`)}if(n.nakshatra?.name){let r=n.nakshatra.pada?` pada ${n.nakshatra.pada}`:"";e.push(`${n.nakshatra.name}${r}`)}return n.awastha&&e.push(n.awastha),n.isRetrograde&&e.push("retrograde"),e.join(" \xB7 ")}function Lt(n,a,e,t,r){let o=t-(n.length-1)*r/2;return n.map((i,d)=>{let c=o+d*r;return k`<text class="planet-text" x=${e} y=${c} text-anchor="middle" dominant-baseline="central">${Br(i,a)}<title>${Kr(i,a)}</title></text>`})}function nt(n,a){let e={};for(let r of _)e[r.toLowerCase()]=[];let t="";for(let[r,o]of Object.entries(n??{})){let i=(o?.rashi??"").toLowerCase();if(r==="Lagna"||o?.graha==="Lagna"){t=jr[i]??"";continue}!i||!(i in e)||e[i]?.push({graha:o.graha??r,longitude:o.longitude,nakshatra:o.nakshatra,isRetrograde:o.isRetrograde,awastha:o.awastha})}return{lagnaSign:t,placements:e,divisionLabel:a}}var Pe=O/4,qr={Pisces:{col:0,row:0},Aries:{col:1,row:0},Taurus:{col:2,row:0},Gemini:{col:3,row:0},Cancer:{col:3,row:1},Leo:{col:3,row:2},Virgo:{col:3,row:3},Libra:{col:2,row:3},Scorpio:{col:1,row:3},Sagittarius:{col:0,row:3},Capricorn:{col:0,row:2},Aquarius:{col:0,row:1}};function Vr(n){let a=qr[n]??{col:0,row:0};return{x:T+a.col*Pe,y:T+a.row*Pe,w:Pe,h:Pe}}function Yr(n){let a=T,e=T+Pe,t=T+2*Pe,r=T+3*Pe,o=j-T;return k`
		<rect class="line" x=${a} y=${a} width=${O} height=${O} stroke-width="1.5" fill="none" />
		<line class="line" x1=${a} y1=${e} x2=${o} y2=${e} stroke-width="1" />
		<line class="line" x1=${a} y1=${r} x2=${o} y2=${r} stroke-width="1" />
		<line class="line" x1=${e} y1=${a} x2=${e} y2=${o} stroke-width="1" />
		<line class="line" x1=${r} y1=${a} x2=${r} y2=${o} stroke-width="1" />
		<line class="line" x1=${a} y1=${t} x2=${e} y2=${t} stroke-width="1" />
		<line class="line" x1=${r} y1=${t} x2=${o} y2=${t} stroke-width="1" />
		<line class="line" x1=${t} y1=${a} x2=${t} y2=${e} stroke-width="1" />
		<line class="line" x1=${t} y1=${r} x2=${t} y2=${o} stroke-width="1" />
		${n?k`<text class="centre-label" x=${N} y=${N} text-anchor="middle" dominant-baseline="central">${n}</text>`:l}
	`}function lr(n,a){let e=_.findIndex(r=>r===a),t=_.findIndex(r=>r===n);return e===-1||t===-1?0:(t-e+12)%12+1}function Ur(n,a,e,t){let r=Vr(n),o=r.x+r.w/2,i=r.y+r.h/2,d=kt[n]??n.slice(0,2),c=14;return k`
		<g class=${e?"cell lagna":"cell"}>
			${e?k`
						<rect class="lagna-bg" x=${r.x} y=${r.y} width=${r.w} height=${r.h} />
						<line class="lagna-slash" x1=${r.x+r.w-c} y1=${r.y+c} x2=${r.x+c} y2=${r.y+r.h-c} stroke-width="1.2" />
					`:l}
			<text class="sign-text" x=${r.x+6} y=${r.y+12} text-anchor="start" dominant-baseline="central">${d}</text>
			${t>0?k`<text class="house-num" x=${r.x+r.w-6} y=${r.y+12} text-anchor="end" dominant-baseline="central">${t}</text>`:l}
			${a.length?Lt(a,n,o,i+4,14):l}
		</g>
	`}function Fr(n){let a=n.lagnaSign.toLowerCase();return k`
		${Yr(n.divisionLabel)}
		${_.map(e=>Ur(e,n.placements[e.toLowerCase()]??[],e.toLowerCase()===a,lr(e,n.lagnaSign)))}
	`}var L={tl:{x:T,y:T},tr:{x:j-T,y:T},br:{x:j-T,y:j-T},bl:{x:T,y:j-T},top:{x:N,y:T},right:{x:j-T,y:N},bottom:{x:N,y:j-T},left:{x:T,y:N},tlMid:{x:N-O/4,y:N-O/4},trMid:{x:N+O/4,y:N-O/4},brMid:{x:N+O/4,y:N+O/4},blMid:{x:N-O/4,y:N+O/4}};function X(n){let a=n.reduce((t,r)=>t+r.x,0)/n.length,e=n.reduce((t,r)=>t+r.y,0)/n.length;return{x:a,y:e}}var Wr={1:{x:N,y:L.tlMid.y},2:X([L.tl,L.top,L.tlMid]),3:X([L.tl,L.left,L.tlMid]),4:{x:L.tlMid.x,y:N},5:X([L.bl,L.left,L.blMid]),6:X([L.bl,L.bottom,L.blMid]),7:{x:N,y:L.blMid.y},8:X([L.br,L.bottom,L.brMid]),9:X([L.br,L.right,L.brMid]),10:{x:L.brMid.x,y:N},11:X([L.tr,L.right,L.trMid]),12:X([L.tr,L.top,L.trMid])};function Jr(n,a){let e=_.findIndex(t=>t===a);return e===-1?n:(e+n-1)%12+1}function Xr(n){let{tl:a,tr:e,br:t,bl:r,top:o,right:i,bottom:d,left:c}=L;return k`
		<rect class="line" x=${a.x} y=${a.y} width=${O} height=${O} stroke-width="1.5" fill="none" />
		<polygon class="line" points="${o.x},${o.y} ${i.x},${i.y} ${d.x},${d.y} ${c.x},${c.y}" stroke-width="1" fill="none" />
		<line class="line" x1=${a.x} y1=${a.y} x2=${t.x} y2=${t.y} stroke-width="1" />
		<line class="line" x1=${e.x} y1=${e.y} x2=${r.x} y2=${r.y} stroke-width="1" />
		${n?k`<text class="centre-label" x=${N} y=${N} text-anchor="middle" dominant-baseline="central">${n}</text>`:l}
	`}function Zr(n,a,e,t,r){let o=Wr[n];if(!o)return k``;let i=Math.min(14,Math.abs(o.y-N)*.45+6),d=i+12;return k`
		<g class=${r?"cell lagna":"cell"}>
			<text class="rashi-num" x=${o.x} y=${o.y-i} text-anchor="middle" dominant-baseline="central">${a}</text>
			${r?k`<text class="lagna-marker" x=${o.x} y=${o.y-d} text-anchor="middle" dominant-baseline="central">Asc</text>`:l}
			${t.length?Lt(t,e,o.x,o.y+8,12):l}
		</g>
	`}function Qr(n){let a=n.lagnaSign||"Aries";return k`
		${Xr(n.divisionLabel)}
		${Array.from({length:12},(e,t)=>{let r=t+1,o=Jr(r,a),i=_[o-1]??"Aries";return Zr(r,o,i,n.placements[i.toLowerCase()]??[],r===1)})}
	`}var ot=O/3;function ea(){let n=T,a=T+ot,e=T+2*ot,t=j-T,Ue={Aries:[{x:a,y:n},{x:e,y:n},{x:e,y:a},{x:a,y:a}],Taurus:[{x:n,y:n},{x:a,y:n},{x:a,y:a}],Gemini:[{x:n,y:n},{x:a,y:a},{x:n,y:a}],Cancer:[{x:n,y:a},{x:a,y:a},{x:a,y:e},{x:n,y:e}],Leo:[{x:n,y:e},{x:a,y:e},{x:n,y:t}],Virgo:[{x:a,y:e},{x:a,y:t},{x:n,y:t}],Libra:[{x:a,y:e},{x:e,y:e},{x:e,y:t},{x:a,y:t}],Scorpio:[{x:e,y:e},{x:e,y:t},{x:t,y:t}],Sagittarius:[{x:e,y:e},{x:t,y:t},{x:t,y:e}],Capricorn:[{x:e,y:a},{x:t,y:a},{x:t,y:e},{x:e,y:e}],Aquarius:[{x:t,y:n},{x:t,y:a},{x:e,y:a}],Pisces:[{x:e,y:n},{x:t,y:n},{x:e,y:a}]},De={};for(let[Fe,We]of Object.entries(Ue))De[Fe]={points:[...We],centroid:X(We)};return De}var ta=ea();function ra(n){let a=T,e=T+ot,t=T+2*ot,r=j-T;return k`
		<rect class="line" x=${a} y=${a} width=${O} height=${O} stroke-width="1.5" fill="none" />
		<line class="line" x1=${a} y1=${e} x2=${e} y2=${e} stroke-width="1" />
		<line class="line" x1=${t} y1=${e} x2=${r} y2=${e} stroke-width="1" />
		<line class="line" x1=${a} y1=${t} x2=${e} y2=${t} stroke-width="1" />
		<line class="line" x1=${t} y1=${t} x2=${r} y2=${t} stroke-width="1" />
		<line class="line" x1=${e} y1=${a} x2=${e} y2=${e} stroke-width="1" />
		<line class="line" x1=${e} y1=${t} x2=${e} y2=${r} stroke-width="1" />
		<line class="line" x1=${t} y1=${a} x2=${t} y2=${e} stroke-width="1" />
		<line class="line" x1=${t} y1=${t} x2=${t} y2=${r} stroke-width="1" />
		<line class="line" x1=${a} y1=${a} x2=${e} y2=${e} stroke-width="1" />
		<line class="line" x1=${r} y1=${a} x2=${t} y2=${e} stroke-width="1" />
		<line class="line" x1=${r} y1=${r} x2=${t} y2=${t} stroke-width="1" />
		<line class="line" x1=${a} y1=${r} x2=${e} y2=${t} stroke-width="1" />
		${n?k`<text class="centre-label" x=${N} y=${N} text-anchor="middle" dominant-baseline="central">${n}</text>`:l}
	`}function aa(n,a,e,t){let r=ta[n];if(!r)return k``;let{centroid:o,points:i}=r,d=kt[n]??n.slice(0,2),c=i.map(m=>`${m.x},${m.y}`).join(" ");return k`
		<g class=${e?"cell lagna":"cell"}>
			${e?k`<polygon class="lagna-bg" points=${c} />`:l}
			<text class="sign-text" x=${o.x} y=${o.y-16} text-anchor="middle" dominant-baseline="central">${d}</text>
			${t>0?k`<text class="house-num" x=${o.x+18} y=${o.y-16} text-anchor="start" dominant-baseline="central">${t}</text>`:l}
			${e?k`<text class="lagna-marker" x=${o.x} y=${o.y-30} text-anchor="middle" dominant-baseline="central">Asc</text>`:l}
			${a.length?Lt(a,n,o.x,o.y+4,12):l}
		</g>
	`}function sa(n){let a=n.lagnaSign.toLowerCase();return k`
		${ra(n.divisionLabel)}
		${_.map(e=>aa(e,n.placements[e.toLowerCase()]??[],e.toLowerCase()===a,lr(e,n.lagnaSign)))}
	`}function it(n,a){switch(a){case"north":return Qr(n);case"east":return sa(n);default:return Fr(n)}}function lt(n,a){return s`<div
		class="kundli-tablist"
		role="tablist"
		aria-label="Kundli style"
		@keydown=${t=>{let r=Le.findIndex(o=>o.id===n);if(t.key==="ArrowRight"){t.preventDefault();let o=Le[(r+1)%Le.length];o&&a(o.id)}else if(t.key==="ArrowLeft"){t.preventDefault();let o=Le[(r-1+Le.length)%Le.length];o&&a(o.id)}}}
	>
		${Le.map(t=>s`<button
				type="button"
				class="kundli-tab"
				role="tab"
				id="kundli-tab-${t.id}"
				aria-selected=${n===t.id?"true":"false"}
				tabindex=${n===t.id?"0":"-1"}
				@click=${()=>a(t.id)}
			>
				${t.label}
			</button>`)}
	</div>`}var dt=f`
	.wrap {
		display: grid;
		gap: var(--roxy-space-md, 1rem);
	}
	.header {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: var(--roxy-space-sm, 0.5rem);
	}
	.title {
		font-size: var(--roxy-text-lg, 1.125rem);
		font-weight: var(--roxy-weight-bold, 600);
		margin: 0;
	}
	.kundli-tablist {
		display: inline-flex;
		gap: 2px;
		border-bottom: 2px solid var(--roxy-border, #e4e4e7);
	}
	.kundli-tab {
		padding: var(--roxy-space-xs, 0.25rem) var(--roxy-space-md, 1rem);
		font-size: var(--roxy-text-sm, 0.875rem);
		background: none;
		border: none;
		border-bottom: 2px solid transparent;
		margin-bottom: -2px;
		cursor: pointer;
		color: var(--roxy-muted, #71717a);
		font-family: inherit;
		transition: color var(--roxy-motion-duration, 200ms)
			var(--roxy-motion-easing, ease);
	}
	.kundli-tab[aria-selected='true'] {
		color: var(--roxy-accent-fg, #b45309);
		border-bottom-color: var(--roxy-accent, #f59e0b);
		font-weight: var(--roxy-weight-bold, 600);
	}
	.kundli-tab:hover:not([aria-selected='true']) {
		color: var(--roxy-fg, #0a0a0a);
	}
	.kundli-tab:focus-visible {
		outline: 2px solid var(--roxy-ring, rgba(245, 158, 11, 0.4));
		outline-offset: 2px;
		border-radius: 4px;
	}
	svg {
		display: block;
		width: 100%;
		max-width: 560px;
		aspect-ratio: 1 / 1;
		height: auto;
		margin: 0 auto;
	}
	.line {
		fill: transparent;
		stroke: var(--roxy-border, #d4d4d8);
	}
	.sign-text {
		fill: var(--roxy-muted, #71717a);
		font-size: 11px;
		font-weight: 500;
		font-family: var(--roxy-font-sans);
		text-transform: uppercase;
		letter-spacing: 0.04em;
	}
	.rashi-num {
		fill: var(--roxy-muted, #71717a);
		font-size: 12px;
		font-weight: 500;
		font-family: var(--roxy-font-sans);
	}
	.house-num {
		fill: var(--roxy-accent-fg, #b45309);
		font-size: 11px;
		font-weight: 600;
		font-family: var(--roxy-font-sans);
		opacity: 0.85;
	}
	.planet-text {
		fill: var(--roxy-fg, #0a0a0a);
		font-size: 13px;
		font-weight: 600;
		font-family: var(--roxy-font-sans);
	}
	.centre-label {
		fill: var(--roxy-muted, #71717a);
		font-size: 14px;
		font-weight: 600;
		font-family: var(--roxy-font-sans);
		letter-spacing: 0.02em;
	}
	.lagna-marker {
		fill: var(--roxy-accent-fg, #b45309);
		font-size: 10px;
		font-weight: 700;
		font-family: var(--roxy-font-sans);
		letter-spacing: 0.08em;
		text-transform: uppercase;
	}
	.lagna-bg {
		fill: color-mix(in srgb, var(--roxy-accent, #f59e0b) 14%, transparent);
	}
	.lagna-slash {
		stroke: var(--roxy-accent, #f59e0b);
		stroke-linecap: round;
		opacity: 0.7;
	}
`;var Z=class extends x{constructor(){super();this.data=null;this.chartStyle="north";this.setStyle=e=>{this.chartStyle=e};new $(this)}viewModel(){if(!this.data?.chart?.meta)return null;let{division:e}=this.data,t=`D${e.number} ${e.name}`;return nt(this.data.chart.meta,t)}render(){let e=this.viewModel();if(!this.data||!e)return s`<div class="roxy-empty" role="status">No divisional chart data</div>`;let{division:t,vargottama:r}=this.data;return s`<div class="wrap">
			<div class="header">
				<div>
					<h2 class="title">
						D${t.number} ${t.name}
						${t.sanskritName&&t.sanskritName!==t.name?s`<span class="division-meta"> · ${t.sanskritName}</span>`:l}
					</h2>
					${t.significance?s`<p class="significance">${t.significance}</p>`:l}
				</div>
				${lt(this.chartStyle,this.setStyle)}
			</div>

			<svg
				viewBox="0 0 400 400"
				preserveAspectRatio="xMidYMid meet"
				role="img"
				aria-label="D${t.number} ${t.name} divisional chart with twelve sign houses"
			>
				<title>D${t.number} ${t.name}</title>
				${it(e,this.chartStyle)}
			</svg>

			${r&&r.length>0?s`<div class="vargottama-row" role="list" aria-label="Vargottama planets">
						<span class="vargottama-label">Vargottama:</span>
						${r.map(o=>s`<span class="vargottama-pill" role="listitem">
									${M[o]??""} ${o}
								</span>`)}
					</div>`:l}
		</div>`}};Z.styles=[v,dt,f`
			.division-meta {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
				margin: 0;
			}
			.significance {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
				border-left: 2px solid var(--roxy-border, #e4e4e7);
				padding-left: var(--roxy-space-sm, 0.5rem);
				margin: 0;
			}
			.vargottama-row {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
				align-items: center;
			}
			.vargottama-label {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
				font-weight: 500;
				margin-right: var(--roxy-space-xs, 0.25rem);
			}
			.vargottama-pill {
				display: inline-flex;
				align-items: center;
				gap: 0.2em;
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: 600;
				padding: 0.15em 0.6em;
				border-radius: 999px;
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 22%, transparent);
				color: var(--roxy-fg, #0a0a0a);
				border: 1px solid color-mix(in srgb, var(--roxy-accent, #f59e0b) 45%, transparent);
			}
		`],p([u({attribute:!1})],Z.prototype,"data",2),p([u({type:String,reflect:!0,attribute:"chart-style"})],Z.prototype,"chartStyle",2),Z=p([b("roxy-divisional-chart")],Z);var oa={manglik:"Mangal Dosha",kalsarpa:"Kaal Sarp Dosha",sadhesati:"Sade Sati"},Q=class extends x{constructor(){super();this.data=null;this.type="manglik";new $(this)}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No dosha data</div>`;let t=!!e.present,r=oa[this.type]??this.type,o=(e.severity??"").toLowerCase(),i=o==="severe"?3:o==="moderate"?2:o==="mild"?1:0,d=i*33,c=i===3?"var(--roxy-danger)":i===2?"var(--roxy-warning)":i===1?"var(--roxy-success)":"transparent";return s`<article
			class="card"
			aria-label=${r}
		>
			<header class="head">
				<h2 class="title">${r}</h2>
				<span class=${`badge ${t?"present":"absent"}`}>
					${t?"Present":"Absent"}
				</span>
			</header>
			${e.severity?s`<div
						class="severity-bar"
						role="meter"
						aria-valuemin="0"
						aria-valuemax="3"
						aria-valuenow="${i}"
						aria-label="Severity ${e.severity}"
					>
						<span class="severity-fill" style="width: ${d}%; background: ${c};"></span>
					</div>`:l}
			${e.description?s`<p class="description">${e.description}</p>`:l}
			${this.renderEffects(e)}
			${e.remedies&&e.remedies.length>0?s`<div>
						<h3>Remedies</h3>
						<ul>
							${e.remedies.map(m=>s`<li>${m}</li>`)}
						</ul>
					</div>`:l}
			${"exceptions"in e&&e.exceptions&&e.exceptions.length>0?s`<div>
					<h3>Exceptions</h3>
					<ul>
						${e.exceptions.map(m=>s`<li>${m}</li>`)}
					</ul>
				</div>`:l}
		</article>`}renderEffects(e){if(!e.effects)return l;let t=Object.entries(e.effects).filter(([,r])=>typeof r=="string"&&r.length>0);return t.length===0?l:s`<div class="effects">
			${t.map(([r,o])=>s`<div>
					<h3>${r}</h3>
					<p>${o}</p>
				</div>`)}
		</div>`}};Q.styles=[v,f`
			.card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}
			.head {
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: var(--roxy-space-md, 1rem);
				flex-wrap: wrap;
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: capitalize;
			}
			.badge {
				display: inline-flex;
				align-items: center;
				gap: var(--roxy-space-xs, 0.25rem);
				padding: 4px 10px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.badge.absent {
				background: color-mix(in srgb, var(--roxy-success, #16a34a) 16%, transparent);
				color: var(--roxy-success-fg, #166534);
			}
			.badge.present {
				background: color-mix(in srgb, var(--roxy-danger, #dc2626) 16%, transparent);
				color: var(--roxy-danger-fg, #991b1b);
			}
			.severity-bar {
				position: relative;
				width: 100%;
				height: 8px;
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 30%, transparent);
				border-radius: 4px;
				overflow: hidden;
			}
			.severity-fill {
				display: block;
				height: 100%;
				transition: width var(--roxy-motion-duration, 200ms) ease-out;
				border-radius: 4px;
			}
			@media (prefers-reduced-motion: reduce) {
				.severity-fill {
					transition: none;
				}
			}

			.description {
				margin: 0;
				color: var(--roxy-fg, #0a0a0a);
			}

			h3 {
				margin: 0 0 var(--roxy-space-xs, 0.25rem);
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			ul {
				margin: 0;
				padding-left: var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.effects {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
				gap: var(--roxy-space-md, 1rem);
			}
			.effects p {
				margin: 0;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
		`],p([u({attribute:!1})],Q.prototype,"data",2),p([u({type:String,reflect:!0})],Q.prototype,"type",2),Q=p([b("roxy-dosha-card")],Q);var Pt=new Map;async function na(n){let a=Pt.get(n);return a||(a=fetch(n).then(async e=>{if(!e.ok)throw new Error(`HTTP ${e.status}`);return await e.json()}).catch(e=>{throw Pt.delete(n),e}),Pt.set(n,a)),a}var H=class extends x{constructor(){super(...arguments);this.endpoint="vedic-astrology/birth-chart";this.method="POST";this.specUrl="https://roxyapi.com/api/v2/openapi.json";this.submitLabel="Submit";this.fields=[];this.values={};this.hasLocation=!1;this.loaded=!1;this.specError=null;this.retryLoadSchema=()=>{this.loaded=!1,this.specError=null,this.loadSchema()};this.onLocation=e=>{let t=e.detail;t&&(this.values={...this.values,latitude:t.latitude,longitude:t.longitude,timezone:t.timezone??t.utcOffset})};this.onSubmit=e=>{e.preventDefault();let t=this.fields.filter(r=>r.required).filter(r=>this.values[r.name]===void 0||this.values[r.name]==="");if(t.length>0){this.dispatchEvent(new CustomEvent("roxy-validation-error",{detail:{missing:t.map(r=>r.name)},bubbles:!0,composed:!0}));return}this.dispatchEvent(new CustomEvent("roxy-submit",{detail:{endpoint:this.endpoint,values:this.values},bubbles:!0,composed:!0}))}}connectedCallback(){super.connectedCallback(),this.loadSchema()}async loadSchema(){this.specError=null;try{let e=await na(this.specUrl),t=`/${this.endpoint.replace(/^\//,"")}`,r=e.paths?.[t]?.[this.method.toLowerCase()];if(!r)throw new Error(`Endpoint ${this.method} ${t} not found in OpenAPI spec`);let o=e.components?.schemas??{},i=[],d;if(r.requestBody){let m=r.requestBody.content?.["application/json"]?.schema;d=this.resolve(m,o)}if(d?.properties){let m=new Set(d.required??[]);for(let[g,h]of Object.entries(d.properties)){let y=this.resolve(h,o)??{};i.push({name:g,type:this.fieldType(y),required:m.has(g),description:y.description,enum:y.enum,min:y.minimum,max:y.maximum,default:y.default})}}for(let m of r.parameters??[])if(m.in==="path"||m.in==="query"){let g=this.resolve(m.schema,o)??{};i.push({name:m.name,type:this.fieldType(g),required:!!m.required,description:g.description,enum:g.enum,default:g.default})}this.fields=i,this.hasLocation=i.some(m=>m.name==="latitude")&&i.some(m=>m.name==="longitude")&&i.some(m=>m.name==="timezone");let c={};for(let m of i)m.default!==void 0&&(c[m.name]=m.default);this.values=c,this.loaded=!0}catch(e){let t=e instanceof Error?e.message:String(e);this.specError=t,this.loaded=!0,this.dispatchEvent(new CustomEvent("roxy-spec-error",{detail:{url:this.specUrl,message:t},bubbles:!0,composed:!0}))}}resolve(e,t){if(e){if("$ref"in e&&e.$ref){let r=e.$ref.split("/").pop();return r?t[r]:void 0}return e}}fieldType(e){return e.enum?"enum":e.format==="date"?"date":e.format==="time"?"time":e.format==="date-time"?"datetime":e.type==="integer"||e.type==="number"?"number":"text"}setValue(e,t){this.values={...this.values,[e]:t}}render(){if(!this.loaded)return s`<form><div class="roxy-skeleton" style="height: 8rem"></div></form>`;if(this.specError)return s`<div class="spec-error" role="alert">
				Schema load failed: ${this.specError}
				<button type="button" class="submit" @click=${this.retryLoadSchema}>Retry</button>
			</div>`;let e=t=>{if(this.hasLocation&&(t.name==="latitude"||t.name==="longitude"||t.name==="timezone"))return l;let r=`roxy-form-${t.name}`;return s`<div class="field">
				<label for=${r}>
					${pe(t.name)}${t.required?s`<span class="req" aria-hidden="true">*</span>`:l}
				</label>
				${t.enum?s`<select
							id=${r}
							?required=${t.required}
							@change=${o=>this.setValue(t.name,o.target.value)}
						>
							<option value="">Choose</option>
							${t.enum.map(o=>s`<option value=${o} ?selected=${this.values[t.name]===o}>
									${o}
								</option>`)}
						</select>`:s`<input
							id=${r}
							type=${this.htmlType(t.type)}
							?required=${t.required}
							min=${t.min??""}
							max=${t.max??""}
							step=${t.type==="number"?"any":""}
							.value=${this.values[t.name]??""}
							@input=${o=>this.setValue(t.name,this.coerce(t.type,o.target.value))}
						/>`}
				${t.description?s`<small class="help">${t.description}</small>`:l}
			</div>`};return s`<form @submit=${this.onSubmit}>
			<h2 class="title">${pe(this.endpoint.split("/").pop()??"")}</h2>
			${this.hasLocation?s`<div class="location-block">
						<label>Birth location</label>
						<roxy-location-search
							@roxy-location-select=${this.onLocation}
							placeholder="City of birth"
						></roxy-location-search>
						<small class="help">
							Required: latitude, longitude, timezone. Pick a city to autofill.
						</small>
					</div>`:l}
			<div class="fields">
				${this.fields.map(t=>e(t))}
			</div>
			<button class="submit" type="submit">${this.submitLabel}</button>
		</form>`}htmlType(e){switch(e){case"date":return"date";case"time":return"time";case"datetime":return"datetime-local";case"number":return"number";default:return"text"}}coerce(e,t){if(t!==""){if(e==="number"){let r=Number(t);return Number.isFinite(r)?r:void 0}return t}}};H.styles=[v,f`
			form {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.fields {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
				align-items: start;
				gap: var(--roxy-space-md, 1rem);
			}
			.field {
				display: flex;
				flex-direction: column;
				gap: var(--roxy-space-xs, 0.25rem);
				min-width: 0;
			}
			label {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-secondary, #475569);
			}
			label .req {
				color: var(--roxy-danger-fg, #991b1b);
				margin-left: 4px;
			}
			input,
			select {
				width: 100%;
				box-sizing: border-box;
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-base, 1rem);
				font-family: inherit;
				color: var(--roxy-fg, #0a0a0a);
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
			}
			input:focus,
			select:focus {
				outline: 2px solid var(--roxy-ring, rgba(245, 158, 11, 0.4));
				outline-offset: 2px;
				border-color: var(--roxy-accent-fg, #b45309);
			}
			.help {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-xs, 0.75rem);
			}
			.location-block {
				display: grid;
				gap: var(--roxy-space-xs, 0.25rem);
				grid-column: 1 / -1;
			}
			.coords {
				display: grid;
				grid-template-columns: repeat(3, 1fr);
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.coords input {
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			button.submit {
				justify-self: start;
				background: var(--roxy-accent-fg, #b45309);
				color: var(--roxy-bg, #fff);
				border: 0;
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-lg, 1.5rem);
				font-size: var(--roxy-text-base, 1rem);
				font-weight: var(--roxy-weight-bold, 600);
				cursor: pointer;
				transition:
					transform var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}
			button.submit:hover {
				transform: scale(1.02);
			}
			button.submit:focus-visible {
				outline: 2px solid var(--roxy-ring, rgba(245, 158, 11, 0.4));
				outline-offset: 2px;
			}
			.spec-error {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
				justify-items: start;
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-danger, #dc2626);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				color: var(--roxy-danger-fg, #991b1b);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
		`],p([u({type:String,attribute:"data-endpoint"})],H.prototype,"endpoint",2),p([u({type:String})],H.prototype,"method",2),p([u({type:String,attribute:"spec-url"})],H.prototype,"specUrl",2),p([u({type:String,attribute:"submit-label"})],H.prototype,"submitLabel",2),p([z()],H.prototype,"fields",2),p([z()],H.prototype,"values",2),p([z()],H.prototype,"hasLocation",2),p([z()],H.prototype,"loaded",2),p([z()],H.prototype,"specError",2),H=p([b("roxy-endpoint-form")],H);var he=class extends x{constructor(){super();this.data=null;new $(this)}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No Guna Milan data</div>`;let t=(e.breakdown??[]).filter(h=>h?.category!==void 0),r=e.total??0,o=e.maxScore??36,i=r/o*100,d="color-mix(in srgb, var(--roxy-border) 50%, transparent)",c=i>=70?"var(--roxy-success)":i>=50?"var(--roxy-warning)":"var(--roxy-danger)",m=i*2.827,g=(100-i)*2.827;return s`<article class="card" aria-label="Guna Milan score">
			<div class="score-header">
				<div class="score-info">
					<div class="score-bar">
						<div>
							<span class="total">${C(e.total,1)}</span>
							<span class="over"> / ${e.maxScore}</span>
							${typeof e.percentage=="number"?s`<small style="margin-left: 0.5rem; color: var(--roxy-muted)">
										${sr(e.percentage,1)}
									</small>`:l}
						</div>
						${e.recommendation?s`<span class="recommendation">${e.recommendation}</span>`:l}
					</div>
				</div>
				<div class="score-ring" role="meter" aria-label="Guna milan score" aria-valuemin="0" aria-valuemax="36" aria-valuenow="${r}">
					<svg viewBox="0 0 100 100" aria-hidden="true">
						<circle class="ring-track" cx="50" cy="50" r="45" fill="none" stroke="${d}" stroke-width="8"/>
						<circle class="ring-fill" cx="50" cy="50" r="45" fill="none" stroke="${c}" stroke-width="8"
								stroke-dasharray="${m},${g}" stroke-linecap="round"
								transform="rotate(-90 50 50)"/>
						<text x="50" y="50" text-anchor="middle" dominant-baseline="central" class="ring-text">${r}</text>
						<text x="50" y="64" text-anchor="middle" dominant-baseline="central" class="ring-max">/${o}</text>
					</svg>
				</div>
			</div>

			${t.length>0?s`<table>
						<thead>
							<tr>
								<th>Category</th>
								<th>Progress</th>
								<th class="score">Score</th>
							</tr>
						</thead>
						<tbody>
							${t.map(h=>{let y=h.score??0,S=h.maxScore??ia(h.category),w=S?y/S*100:0;return s`<tr>
									<td>${h.category}</td>
									<td class="bar-cell">
										<div class="mini-bar">
											<span style="width: ${w}%"></span>
										</div>
									</td>
									<td class="score">${C(y,1)} / ${S}</td>
								</tr>`})}
						</tbody>
					</table>`:l}
			${(e.doshas?.length??0)>0||(e.doshaCancellations?.length??0)>0?s`<div class="tags">
						${e.doshas?.map(h=>s`<span class="dosha">${h}</span>`)}
						${e.doshaCancellations?.map(h=>s`<span class="cancel" title=${h.reason}>${h.dosha} cancelled</span>`)}
					</div>`:l}
		</article>`}};he.styles=[v,f`
			.card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.score-header {
				display: flex;
				align-items: center;
				gap: 1rem;
			}
			.score-info {
				flex: 1;
			}
			.score-bar {
				display: grid;
				grid-template-columns: 1fr auto;
				align-items: center;
				gap: var(--roxy-space-md, 1rem);
			}
			.total {
				font-size: 2.25rem;
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-accent-fg, #b45309);
				font-variant-numeric: tabular-nums;
				line-height: 1;
			}
			.over {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-base, 1rem);
			}
			.recommendation {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-secondary, #475569);
			}
			.score-ring {
				width: 120px;
				height: 120px;
				flex-shrink: 0;
			}
			.score-ring svg {
				width: 100%;
				height: 100%;
			}
			.score-ring .ring-text {
				font-size: 22px;
				font-weight: 700;
				fill: var(--roxy-fg, #0a0a0a);
				font-family: var(--roxy-font-sans);
			}
			.score-ring .ring-max {
				font-size: 10px;
				fill: var(--roxy-muted, #71717a);
				font-family: var(--roxy-font-sans);
			}

			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				text-align: left;
			}
			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.06em;
			}
			td.score {
				text-align: right;
				font-variant-numeric: tabular-nums;
				color: var(--roxy-fg, #0a0a0a);
				font-weight: var(--roxy-weight-bold, 600);
			}
			td.bar-cell {
				width: 30%;
			}
			.mini-bar {
				height: 8px;
				background: var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-full, 9999px);
				overflow: hidden;
			}
			.mini-bar > span {
				display: block;
				height: 100%;
				background: var(--roxy-accent, #f59e0b);
				transition:
					width var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}

			.tags {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.tags span {
				padding: 2px 8px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
			}
			.tags .dosha {
				background: color-mix(in srgb, var(--roxy-danger, #dc2626) 16%, transparent);
				color: var(--roxy-danger-fg, #991b1b);
			}
			.tags .cancel {
				background: color-mix(in srgb, var(--roxy-success, #16a34a) 18%, transparent);
				color: var(--roxy-success-fg, #166534);
			}
		`],p([u({attribute:!1})],he.prototype,"data",2),he=p([b("roxy-guna-milan")],he);function ia(n){if(!n)return 1;switch(n.toLowerCase()){case"varna":return 1;case"vasya":return 2;case"tara":return 3;case"yoni":return 4;case"maitri":return 5;case"gana":return 6;case"bhakoot":return 7;case"nadi":return 8;default:return 1}}var ee=class extends x{constructor(){super();this.data=null;this.mode="lookup";new $(this)}resolveHexagram(){let e=this.data;if(!e)return null;if("hexagram"in e&&e.hexagram){if("lines"in e){let r=e;return{hex:r.hexagram,lines:r.lines,changingLinePositions:r.changingLinePositions,resultingHexagram:r.resultingHexagram}}let t=e;return{hex:t.hexagram,dailyMessage:t.dailyMessage}}return{hex:e}}render(){let e=this.resolveHexagram();if(!e)return s`<div class="roxy-empty" role="status">No hexagram data</div>`;let{hex:t,lines:r,changingLinePositions:o,dailyMessage:i,resultingHexagram:d}=e,c=r??this.derivedLines(t),m=new Set(o??[]);return s`<article class="card" aria-label="I Ching hexagram">
			<div class="glyphs">
				${t.symbol?s`<div class="symbol">${t.symbol}</div>`:l}
				<div class="lines" aria-hidden="true">
					${c.slice().reverse().map((g,h)=>{let y=c.length-1-h+1,S=m.has(y),w=g===6||g===8;return s`<div class="line ${`${w?"broken":"solid"}${S?" changing":""}`}">
								${w?k`<span class="seg"></span><span class="seg"></span>`:k`<span class="seg"></span>`}
							</div>`})}
				</div>
			</div>
			<div>
				<h2 class="title">
					${t.number?s`${t.number}. `:l}${t.english??t.chinese??"Hexagram"}
				</h2>
				<p class="subtitle">
					${t.chinese?s`${t.chinese}`:l}
					${t.pinyin?s` · ${t.pinyin}`:l}
				</p>
				<div class="trigrams">
					${t.upperTrigram?s`<div>
								Upper
								<span class="tri-glyph"
									>${At[t.upperTrigram]??""}</span
								>${t.upperTrigram}
							</div>`:l}
					${t.lowerTrigram?s`<div>
								Lower
								<span class="tri-glyph"
									>${At[t.lowerTrigram]??""}</span
								>${t.lowerTrigram}
							</div>`:l}
				</div>
				${t.judgment?s`<p class="judgment">${t.judgment}</p>`:l}
				${t.image?s`<p class="image">${t.image}</p>`:l}
				${i?s`<p class="message">${i}</p>`:l}
				${t.interpretation?.general?s`<p>${t.interpretation.general}</p>`:l}
				${m.size>0?s`<div class="changing">
							Changing lines: ${Array.from(m).sort((g,h)=>g-h).join(", ")}.
							${d?.english?s` Becomes hexagram ${d.number}
										${d.english}.`:l}
						</div>`:l}
			</div>
		</article>`}derivedLines(e){let t=e.symbol.codePointAt(0)??0;if(t>=19904&&t<=19967){let r=t-19904,o=[];for(let i=0;i<6;i++){let d=r>>i&1;o.push(d?8:7)}return o}return Array.from({length:6},()=>7)}};ee.styles=[v,f`
			.card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
				display: grid;
				grid-template-columns: 6rem 1fr;
				gap: var(--roxy-space-lg, 1.5rem);
			}

			@container (max-width: 480px) {
				.card {
					grid-template-columns: 1fr;
				}
			}

			.glyphs {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
				justify-items: center;
			}
			.symbol {
				font-size: 3rem;
				line-height: 1;
				color: var(--roxy-accent-fg, #b45309);
			}
			.lines {
				display: grid;
				gap: 4px;
				width: 4rem;
			}
			.line {
				display: flex;
				gap: 4px;
				justify-content: center;
				align-items: center;
				height: 8px;
			}
			.seg {
				display: block;
				height: 6px;
				background: var(--roxy-fg, #0a0a0a);
				border-radius: 1px;
			}
			.line.broken .seg {
				width: 1.4rem;
			}
			.line.solid .seg {
				width: 3rem;
			}
			.line.changing .seg {
				background: var(--roxy-accent, #f59e0b);
			}

			.title {
				margin: 0;
				font-size: var(--roxy-text-xl, 1.5rem);
				font-weight: var(--roxy-weight-bold, 600);
				letter-spacing: var(--roxy-tracking-tight);
			}
			.subtitle {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
				margin: 0 0 var(--roxy-space-sm, 0.5rem);
			}
			.trigrams {
				display: flex;
				gap: var(--roxy-space-md, 1rem);
				margin-bottom: var(--roxy-space-sm, 0.5rem);
				color: var(--roxy-secondary, #475569);
				font-size: var(--roxy-text-xs, 0.75rem);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.tri-glyph {
				font-size: var(--roxy-text-xl, 1.5rem);
				color: var(--roxy-accent-fg, #b45309);
				margin-right: 4px;
				vertical-align: middle;
			}
			.judgment,
			.image,
			.message {
				margin: 0 0 var(--roxy-space-sm, 0.5rem);
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-fg, #0a0a0a);
			}
			.judgment::before {
				content: 'Judgment. ';
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-secondary, #475569);
			}
			.image::before {
				content: 'Image. ';
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-secondary, #475569);
			}

			.changing {
				margin-top: var(--roxy-space-md, 1rem);
				padding-top: var(--roxy-space-md, 1rem);
				border-top: 1px solid var(--roxy-border, #e4e4e7);
				color: var(--roxy-accent-fg, #b45309);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
		`],p([u({attribute:!1})],ee.prototype,"data",2),p([u({type:String,reflect:!0})],ee.prototype,"mode",2),ee=p([b("roxy-hexagram")],ee);var te=class extends x{constructor(){super();this.data=null;this.period="daily";new $(this)}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No horoscope data</div>`;let t=e.sign??"",r=t?G[E(t)]??"":"",o="energyRating"in e&&typeof e.energyRating=="number"?e.energyRating:null,i="date"in e&&e.date||"week"in e&&e.week||"month"in e&&e.month||"";return s`<article
			class="card"
			aria-label=${`${this.period} horoscope for ${t}`}
		>
			<header class="head">
				<span class="glyph" aria-hidden="true">${r}</span>
				<div>
					<h2 class="title">${t} ${this.period}</h2>
					${i?s`<div class="date">${i}</div>`:l}
				</div>
				${o!==null?s`<span class="energy" aria-label=${`Energy ${o} of 10`}>
							Energy ${o}/10
							<span class="energy-bar"
								><span style="width: ${o/10*100}%"></span
							></span>
						</span>`:l}
			</header>

			${e.overview?s`<p class="overview">${e.overview}</p>`:l}

			<div class="sections">
				${e.love?s`<div class="section">
							<h3>Love</h3>
							<p>${e.love}</p>
						</div>`:l}
				${e.career?s`<div class="section">
							<h3>Career</h3>
							<p>${e.career}</p>
						</div>`:l}
				${e.health?s`<div class="section">
							<h3>Health</h3>
							<p>${e.health}</p>
						</div>`:l}
				${e.finance?s`<div class="section">
							<h3>Finance</h3>
							<p>${e.finance}</p>
						</div>`:l}
				${"advice"in e&&e.advice?s`<div class="section">
							<h3>Advice</h3>
							<p>${e.advice}</p>
						</div>`:l}
			</div>

			${(()=>{let d="luckyNumber"in e&&e.luckyNumber!==void 0?e.luckyNumber:void 0,c="luckyColor"in e&&e.luckyColor?e.luckyColor:"",m="luckyNumbers"in e&&e.luckyNumbers?e.luckyNumbers:[],g="luckyDays"in e&&e.luckyDays?e.luckyDays:[],h=e.compatibleSigns??[];return d===void 0&&!c&&m.length===0&&g.length===0&&h.length===0?l:s`<div class="lucky">
						${d!==void 0?s`<span>Lucky number <strong>${d}</strong></span>`:l}
						${c?s`<span>Lucky color <strong>${c}</strong></span>`:l}
						${m.length?s`<span
									>Lucky numbers
									<strong>${m.join(", ")}</strong></span
								>`:l}
						${g.length?s`<span
									>Lucky days <strong>${g.join(", ")}</strong></span
								>`:l}
						${h.length?s`<span class="compat-wrap">
									Best with
									<span class="compat"
										>${h.map(y=>s`<span>${y}</span>`)}</span
									>
								</span>`:l}
					</div>`})()}
		</article>`}};te.styles=[v,f`
			.card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.head {
				display: flex;
				align-items: center;
				gap: var(--roxy-space-md, 1rem);
			}

			.glyph {
				font-size: 2.25rem;
				color: var(--roxy-accent-fg, #b45309);
				line-height: 1;
			}

			.title {
				font-size: var(--roxy-text-xl, 1.5rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
				letter-spacing: var(--roxy-tracking-tight);
				text-transform: capitalize;
			}

			.date {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
			}

			.energy {
				margin-left: auto;
				font-variant-numeric: tabular-nums;
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-secondary, #475569);
			}
			.energy-bar {
				display: inline-block;
				width: 6rem;
				height: 6px;
				background: var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-full, 9999px);
				overflow: hidden;
				margin-left: 6px;
				vertical-align: middle;
			}
			.energy-bar > span {
				display: block;
				height: 100%;
				background: var(--roxy-accent, #f59e0b);
				transition:
					width var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}

			.overview {
				font-size: var(--roxy-text-base, 1rem);
				color: var(--roxy-fg, #0a0a0a);
				margin: 0;
			}

			.sections {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
				gap: var(--roxy-space-md, 1rem);
			}

			.section h3 {
				margin: 0 0 var(--roxy-space-xs, 0.25rem) 0;
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.section p {
				margin: 0;
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-fg, #0a0a0a);
			}

			.lucky {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-sm, 0.5rem);
				border-top: 1px solid var(--roxy-border, #e4e4e7);
				padding-top: var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-secondary, #475569);
			}

			.lucky strong {
				color: var(--roxy-fg, #0a0a0a);
				font-weight: var(--roxy-weight-bold, 600);
			}

			.compat-wrap {
				width: 100%;
				display: flex;
				align-items: center;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.compat {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.compat span {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 16%, transparent);
				color: var(--roxy-fg, #0a0a0a);
				padding: 2px 8px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
				text-transform: capitalize;
			}
		`],p([u({attribute:!1})],te.prototype,"data",2),p([u({type:String,reflect:!0})],te.prototype,"period",2),te=p([b("roxy-horoscope-card")],te);var re=class extends x{constructor(){super();this.data=null;this.activeTab="planets";new $(this)}bodies(){let e=this.data;if(!e)return[];let t=(e.planets??[]).map(o=>({name:o.planet,sign:o.sign,house:o.house,nakshatra:o.nakshatra,starLord:o.starLord,subLord:o.subLord,subSubLord:o.subSubLord,kpNumber:o.kpNumber,retrograde:o.retrograde})),r=e.nodes;for(let[o,i]of[["Rahu",r?.rahu],["Ketu",r?.ketu]])i&&t.push({name:o,sign:i.sign,house:i.house,nakshatra:i.nakshatra,starLord:i.starLord,subLord:i.subLord,subSubLord:i.subSubLord,retrograde:!0});return t}onTabKeyDown(e){if(e.key!=="ArrowRight"&&e.key!=="ArrowLeft")return;e.preventDefault(),this.activeTab=this.activeTab==="planets"?"cusps":"planets";let t=this.activeTab;requestAnimationFrame(()=>{this.shadowRoot?.querySelector(`#tab-${t}`)?.focus()})}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No KP chart data</div>`;let e=this.data,t=e.ascendant;return s`<div class="wrap" aria-label="KP chart" tabindex="0">
			<header class="head">
				<h2 class="title">KP chart</h2>
				${t?s`<div class="asc">
							Ascendant: <strong>${t.sign??""}</strong>
							${t.nakshatra?s`· ${t.nakshatra}`:l}
							${t.subLord?s`· sub lord ${t.subLord}`:l}
							${typeof t.kpNumber=="number"?s`· KP ${t.kpNumber}`:l}
						</div>`:l}
				${typeof e.meta?.ayanamsa=="number"?s`<div class="ayan">
							${e.meta.ayanamsaType??"Ayanamsa"}: ${C(e.meta.ayanamsa,4)}°
							${e.meta.houseSystem?s`· ${e.meta.houseSystem} houses`:l}
						</div>`:l}
			</header>

			<div
				class="tablist"
				role="tablist"
				aria-label="KP chart views"
				@keydown=${this.onTabKeyDown}
			>
				${["planets","cusps"].map(r=>s`<button
						class="tab"
						role="tab"
						id="tab-${r}"
						aria-selected=${this.activeTab===r?"true":"false"}
						aria-controls="panel-${r}"
						tabindex=${this.activeTab===r?"0":"-1"}
						@click=${()=>{this.activeTab=r}}
					>
						${r==="planets"?"Planets":"Cusps"}
					</button>`)}
			</div>

			<div id="panel-${this.activeTab}" role="tabpanel" aria-labelledby="tab-${this.activeTab}">
				${this.activeTab==="planets"?this.renderPlanets():this.renderCusps()}
			</div>
		</div>`}renderPlanets(){let e=this.bodies();return e.length?s`<table role="table" aria-label="KP planets and nodes">
			<thead>
				<tr>
					<th scope="col">Body</th>
					<th scope="col">Sign</th>
					<th scope="col">House</th>
					<th scope="col">Nakshatra</th>
					<th scope="col">Star lord</th>
					<th scope="col">Sub lord</th>
					<th scope="col">Sub sub lord</th>
					<th scope="col">KP no.</th>
				</tr>
			</thead>
			<tbody>
				${e.map(t=>s`<tr>
						<td class="body">
							${t.name}${t.retrograde?s`<span class="retro">R</span>`:l}
						</td>
						<td>${t.sign??""}</td>
						<td class="num">${typeof t.house=="number"?t.house:""}</td>
						<td>${t.nakshatra??""}</td>
						<td>${t.starLord??""}</td>
						<td>${t.subLord??""}</td>
						<td>${t.subSubLord??""}</td>
						<td class="num">${typeof t.kpNumber=="number"?t.kpNumber:""}</td>
					</tr>`)}
			</tbody>
		</table>`:s`<p class="roxy-empty" role="status">No planets</p>`}renderCusps(){let e=this.data?.cusps??[];return e.length?s`<table role="table" aria-label="KP Placidus cusps">
			<thead>
				<tr>
					<th scope="col">House</th>
					<th scope="col">Sign</th>
					<th scope="col">Sign lord</th>
					<th scope="col">Nakshatra</th>
					<th scope="col">Star lord</th>
					<th scope="col">Sub lord</th>
					<th scope="col">Sub sub lord</th>
					<th scope="col">KP no.</th>
				</tr>
			</thead>
			<tbody>
				${e.map(t=>s`<tr>
						<td class="body num">${t.house}</td>
						<td>${t.sign??""}</td>
						<td>${t.signLord??""}</td>
						<td>${t.nakshatra??""}</td>
						<td>${t.starLord??""}</td>
						<td>${t.subLord??""}</td>
						<td>${t.subSubLord??""}</td>
						<td class="num">${typeof t.kpNumber=="number"?t.kpNumber:""}</td>
					</tr>`)}
			</tbody>
		</table>`:s`<p class="roxy-empty" role="status">No cusps</p>`}};re.styles=[v,f`
			.wrap {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				background: var(--roxy-bg, #fff);
				overflow: auto;
				box-shadow: var(--roxy-shadow-sm);
			}
			.head {
				padding: var(--roxy-space-md, 1rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				display: grid;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.asc,
			.ayan {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.asc strong {
				color: var(--roxy-fg, #0a0a0a);
			}
			.tablist {
				display: flex;
				gap: 2px;
				padding: 0 var(--roxy-space-md, 1rem);
				border-bottom: 2px solid var(--roxy-border, #e4e4e7);
			}
			.tab {
				padding: var(--roxy-space-xs, 0.25rem) var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-sm, 0.875rem);
				background: none;
				border: none;
				border-bottom: 2px solid transparent;
				margin-bottom: -2px;
				cursor: pointer;
				color: var(--roxy-muted, #71717a);
				font-family: inherit;
			}
			.tab[aria-selected='true'] {
				color: var(--roxy-accent-fg, #b45309);
				border-bottom-color: var(--roxy-accent, #f59e0b);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.tab:hover:not([aria-selected='true']) {
				color: var(--roxy-fg, #0a0a0a);
			}
			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
				min-width: 620px;
			}
			thead {
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 20%, transparent);
			}
			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				text-align: left;
				white-space: nowrap;
			}
			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.04em;
			}
			tbody tr {
				border-top: 1px solid var(--roxy-border, #e4e4e7);
			}
			td.body {
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-fg, #0a0a0a);
			}
			.retro {
				color: var(--roxy-warning-fg, #9a3412);
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin-left: 4px;
			}
			.num {
				font-variant-numeric: tabular-nums;
			}
		`],p([u({attribute:!1})],re.prototype,"data",2),p([z()],re.prototype,"activeTab",2),re=p([b("roxy-kp-chart")],re);var ge=class extends x{constructor(){super();this.data=null;new $(this)}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No KP data</div>`;let e=this.data.planets??[];return s`<div
			class="wrap"
			aria-label="KP planets table"
			tabindex="0"
		>
			<header class="head">
				<h2 class="title">KP planets</h2>
				${typeof this.data.ayanamsa=="number"?s`<span class="ayanamsa">Ayanamsa: ${C(this.data.ayanamsa,2)}°</span>`:l}
			</header>
			<table role="table">
				<thead>
					<tr>
						<th scope="col">Planet</th>
						<th scope="col">Sign</th>
						<th scope="col">Sign lord</th>
						<th scope="col">Nakshatra</th>
						<th scope="col">Star lord</th>
						<th scope="col">Sub lord</th>
						<th scope="col">Sub sub lord</th>
						<th scope="col">KP no.</th>
					</tr>
				</thead>
				<tbody>
					${e.map(t=>s`<tr>
							<td class="planet">
								${t.planet}
								${t.retrograde?s`<span class="retro">R</span>`:l}
							</td>
							<td>${t.sign??""}</td>
							<td>${t.signLord??""}</td>
							<td>${t.nakshatra??""}</td>
							<td>${t.nakshatraLord??""}</td>
							<td>${t.subLord??""}</td>
							<td>${t.subSubLord??""}</td>
							<td>${t.kpNumber??""}</td>
						</tr>`)}
				</tbody>
			</table>
		</div>`}};ge.styles=[v,f`
			.wrap {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				background: var(--roxy-bg, #fff);
				overflow: auto;
				box-shadow: var(--roxy-shadow-sm);
			}
			.head {
				padding: var(--roxy-space-md, 1rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				display: flex;
				justify-content: space-between;
				flex-wrap: wrap;
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.ayanamsa {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
				min-width: 560px;
			}
			thead {
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 20%, transparent);
			}
			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				text-align: left;
				white-space: nowrap;
			}
			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.04em;
			}
			tbody tr {
				border-top: 1px solid var(--roxy-border, #e4e4e7);
			}
			td.planet {
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-fg, #0a0a0a);
			}
			.retro {
				color: var(--roxy-warning-fg, #9a3412);
				font-size: var(--roxy-text-xs, 0.75rem);
				margin-left: 4px;
			}
		`],p([u({attribute:!1})],ge.prototype,"data",2),ge=p([b("roxy-kp-planets-table")],ge);var ue=class extends x{constructor(){super();this.data=null;new $(this)}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No ruling planets data</div>`;let e=this.data,t=e.significators??[];return s`<div class="wrap" aria-label="KP ruling planets">
			<header>
				<h2 class="title">KP ruling planets</h2>
				${e.dayLord?s`<div class="day-lord">Day lord: <strong>${e.dayLord}</strong></div>`:l}
			</header>

			<div class="groups">
				<div class="group">
					<h3>Moon</h3>
					<dl>
						<dt>Sign lord</dt><dd>${e.moonSignLord??""}</dd>
						<dt>Star lord</dt><dd>${e.moonStarLord??""}</dd>
						<dt>Sub lord</dt><dd>${e.moonSublord??""}</dd>
						<dt>Sub-sub lord</dt><dd>${e.moonSubSublord??""}</dd>
					</dl>
				</div>
				<div class="group">
					<h3>Lagna</h3>
					<dl>
						<dt>Sign lord</dt><dd>${e.lagnaSignLord??""}</dd>
						<dt>Star lord</dt><dd>${e.lagnaStarLord??""}</dd>
						<dt>Sub lord</dt><dd>${e.lagnaSublord??""}</dd>
						<dt>Sub-sub lord</dt><dd>${e.lagnaSubSublord??""}</dd>
					</dl>
				</div>
			</div>

			${e.rulingPlanets?.length?s`<div class="rp-list" role="list" aria-label="Ruling planets by strength">
						<span class="rp-label">Ruling planets</span>
						${e.rulingPlanets.map((r,o)=>s`<span class="rp" role="listitem"><span class="rank">${o+1}</span> ${r}</span>`)}
					</div>`:l}

			${t.length?s`<table aria-label="House significators">
						<thead>
							<tr>
								<th scope="col">Planet</th>
								<th scope="col">Signifies houses</th>
							</tr>
						</thead>
						<tbody>
							${t.map(r=>s`<tr>
									<td>${r.planet}</td>
									<td>${(r.signifies??[]).join(", ")}</td>
								</tr>`)}
						</tbody>
					</table>`:l}
		</div>`}};ue.styles=[v,f`
			.wrap {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				background: var(--roxy-bg, #fff);
				padding: var(--roxy-space-md, 1rem);
				display: grid;
				gap: var(--roxy-space-md, 1rem);
				box-shadow: var(--roxy-shadow-sm);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.day-lord {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.day-lord strong {
				color: var(--roxy-fg, #0a0a0a);
			}
			.groups {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr));
				gap: var(--roxy-space-md, 1rem);
			}
			.group h3 {
				margin: 0 0 var(--roxy-space-xs, 0.25rem);
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.05em;
			}
			.group dl {
				margin: 0;
				display: grid;
				grid-template-columns: auto 1fr;
				gap: 2px var(--roxy-space-sm, 0.5rem);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.group dt {
				color: var(--roxy-muted, #71717a);
			}
			.group dd {
				margin: 0;
				color: var(--roxy-fg, #0a0a0a);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.rp-list {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
				align-items: center;
			}
			.rp-label {
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.05em;
				margin-right: var(--roxy-space-xs, 0.25rem);
			}
			.rp {
				display: inline-flex;
				align-items: center;
				gap: 0.3em;
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: var(--roxy-weight-bold, 600);
				padding: 0.15em 0.6em;
				border-radius: 999px;
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 18%, transparent);
				color: var(--roxy-fg, #0a0a0a);
			}
			.rp .rank {
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-accent-fg, #b45309);
			}
			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			th,
			td {
				padding: var(--roxy-space-xs, 0.25rem) var(--roxy-space-sm, 0.5rem);
				text-align: left;
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
			}
			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.04em;
			}
		`],p([u({attribute:!1})],ue.prototype,"data",2),ue=p([b("roxy-kp-ruling-planets")],ue);function dr(n,a){let e,t=((...r)=>{e&&clearTimeout(e),e=setTimeout(()=>{e=void 0,n(...r)},a)});return t.cancel=()=>{e&&(clearTimeout(e),e=void 0)},t}var D=class extends x{constructor(){super(...arguments);this.endpoint="https://roxyapi.com/api/v2/location/search";this.placeholder="Search city";this.defaultValue="";this.query="";this.results=[];this.isOpen=!1;this.isLoading=!1;this.highlight=-1;this.secretKeyWarned=!1;this.debouncedFetch=dr(e=>{this.fetchResults(e)},300);this.onInput=e=>{let t=e.target.value;if(this.query=t,t.length<2){this.results=[],this.isOpen=!1,this.highlight=-1;return}this.debouncedFetch(t)};this.onKeyDown=e=>{if(!this.isOpen||this.results.length===0){e.key==="ArrowDown"&&this.query.length>=2&&(this.fetchResults(this.query),e.preventDefault());return}if(e.key==="ArrowDown")e.preventDefault(),this.highlight=(this.highlight+1)%this.results.length;else if(e.key==="ArrowUp")e.preventDefault(),this.highlight=(this.highlight-1+this.results.length)%this.results.length;else if(e.key==="Enter"){e.preventDefault();let t=this.results[this.highlight]??this.results[0];t&&this.select(t)}else e.key==="Escape"&&(this.isOpen=!1)}}connectedCallback(){super.connectedCallback(),this.query=this.defaultValue,this.clickOutsideHandler=e=>{e.composedPath().includes(this)||(this.isOpen=!1)},document.addEventListener("mousedown",this.clickOutsideHandler)}disconnectedCallback(){super.disconnectedCallback(),this.clickOutsideHandler&&document.removeEventListener("mousedown",this.clickOutsideHandler),this.debouncedFetch.cancel(),this.abortController&&(this.abortController.abort(),this.abortController=void 0)}warnIfSecretKey(){if(this.secretKeyWarned||!this.apiKey||this.apiKey.startsWith("pk_"))return;this.secretKeyWarned=!0;let e="Possible secret key in client-side <roxy-location-search>; use a `pk_` publishable key with origin allowlist instead.";console.warn(e),this.dispatchEvent(new CustomEvent("roxy-validation-error",{detail:{reason:"possible-secret-key",message:e},bubbles:!0,composed:!0}))}async fetchResults(e){this.warnIfSecretKey(),this.abortController&&this.abortController.abort();let t=new AbortController;this.abortController=t,this.isLoading=!0;try{let r=new URL(this.endpoint);r.searchParams.set("q",e),r.searchParams.set("limit","8");let o={Accept:"application/json"};this.apiKey&&this.publishableKey&&console.warn("[roxy-location-search] both api-key and publishable-key set; using publishable-key. Remove api-key from your widget markup.");let i=this.publishableKey??this.apiKey;i&&(o["X-API-Key"]=i);let d=await fetch(r,{headers:o,signal:t.signal});if(!d.ok)throw new Error(`HTTP ${d.status}`);let c=await d.json();if(t.signal.aborted)return;this.results=c.cities??[],this.isOpen=this.results.length>0,this.highlight=this.results.length>0?0:-1}catch(r){if(r?.name==="AbortError")return;this.results=[],this.isOpen=!1}finally{this.abortController===t&&(this.abortController=void 0),t.signal.aborted||(this.isLoading=!1)}}select(e){this.query=`${e.city}${e.province?`, ${e.province}`:""}, ${e.country}`,this.isOpen=!1,this.results=[],this.dispatchEvent(new CustomEvent("roxy-location-select",{detail:e,bubbles:!0,composed:!0}))}render(){return s`<div class="field">
			<input
				type="text"
				role="combobox"
				aria-expanded=${this.isOpen?"true":"false"}
				aria-controls="roxy-location-listbox"
				aria-activedescendant=${this.isOpen&&this.highlight>=0?`roxy-location-option-${this.highlight}`:""}
				aria-autocomplete="list"
				autocomplete="off"
				placeholder=${this.placeholder}
				.value=${this.query}
				@input=${this.onInput}
				@keydown=${this.onKeyDown}
				@focus=${()=>{this.results.length>0&&(this.isOpen=!0)}}
			/>
			${this.isLoading?s`<span class="spinner" role="status" aria-label="Loading"></span>`:l}
			${this.isOpen?s`<ul
						id="roxy-location-listbox"
						class="results"
						role="listbox"
					>
						${this.results.length===0?s`<li class="empty" role="status">No cities found</li>`:this.results.map((e,t)=>s`<li role="presentation">
										<button
											type="button"
											class="option"
											role="option"
											id=${`roxy-location-option-${t}`}
											aria-selected=${this.highlight===t?"true":"false"}
											@click=${()=>this.select(e)}
											@mouseenter=${()=>{this.highlight=t}}
										>
											<span class="city">${e.city}</span>
											<span class="where"
												>${e.province?s`${e.province}, `:""}${e.country}</span
											>
											<span class="tz"
												>UTC${e.utcOffset>=0?"+":""}${e.utcOffset}</span
											>
										</button>
									</li>`)}
					</ul>`:l}
		</div>`}};D.styles=[v,f`
			:host {
				display: block;
				position: relative;
			}
			.field {
				position: relative;
			}
			input {
				width: 100%;
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-base, 1rem);
				font-family: inherit;
				color: var(--roxy-fg, #0a0a0a);
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				transition:
					border-color var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
				box-sizing: border-box;
			}
			input:focus {
				outline: 2px solid var(--roxy-ring, rgba(245, 158, 11, 0.4));
				outline-offset: 2px;
				border-color: var(--roxy-accent-fg, #b45309);
			}
			.spinner {
				position: absolute;
				right: 12px;
				top: 50%;
				transform: translateY(-50%);
				width: 14px;
				height: 14px;
				border: 2px solid var(--roxy-muted, #71717a);
				border-top-color: transparent;
				border-radius: 50%;
				animation: roxy-spin 700ms linear infinite;
			}
			@keyframes roxy-spin {
				to {
					transform: translateY(-50%) rotate(360deg);
				}
			}
			@media (prefers-reduced-motion: reduce) {
				.spinner {
					animation: none;
				}
			}

			.results {
				position: absolute;
				z-index: 50;
				top: calc(100% + 4px);
				left: 0;
				right: 0;
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				box-shadow: var(--roxy-shadow-md);
				max-height: 22rem;
				overflow-y: auto;
				animation: roxy-fade-in var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}
			.option {
				display: flex;
				align-items: baseline;
				gap: var(--roxy-space-sm, 0.5rem);
				width: 100%;
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				background: transparent;
				border: 0;
				text-align: left;
				font-family: inherit;
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-fg, #0a0a0a);
				cursor: pointer;
				transition: background-color var(--roxy-motion-duration, 200ms);
			}
			.option:hover,
			.option[aria-selected='true'] {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 10%, transparent);
			}
			.option .city {
				font-weight: var(--roxy-weight-bold, 600);
			}
			.option .where {
				color: var(--roxy-muted, #71717a);
				flex-grow: 1;
			}
			.option .tz {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-xs, 0.75rem);
				font-variant-numeric: tabular-nums;
			}
			.empty {
				padding: var(--roxy-space-md, 1rem);
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
		`],p([u({type:String,attribute:"api-key"})],D.prototype,"apiKey",2),p([u({type:String,attribute:"publishable-key"})],D.prototype,"publishableKey",2),p([u({type:String})],D.prototype,"endpoint",2),p([u({type:String})],D.prototype,"placeholder",2),p([u({type:String,attribute:"default-value"})],D.prototype,"defaultValue",2),p([z()],D.prototype,"query",2),p([z()],D.prototype,"results",2),p([z()],D.prototype,"isOpen",2),p([z()],D.prototype,"isLoading",2),p([z()],D.prototype,"highlight",2),D=p([b("roxy-location-search")],D);var ae=class extends x{constructor(){super();this.data=null;this.mode="current";new $(this)}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No moon phase data</div>`;let t="phases"in e?e.phases:"calendar"in e?e.calendar:[];if(this.mode!=="current"&&t.length>0){let r="month"in e?e.month:void 0,o="year"in e?e.year:void 0;return s`<article
				class="card"
				aria-label="Moon phase calendar"
			>
				<h2 class="label">${r??"Moon phases"} ${o??""}</h2>
				<div class="list" role="list">
					${t.map(i=>this.renderListItem(i))}
				</div>
			</article>`}return"phase"in e?this.renderSingle(e):l}renderSingle(e){let t=cr(e.phase);return s`<article class="card" aria-label="Current moon phase">
			<div class="hero">
				<span class="emoji" aria-hidden="true">${t}</span>
				<div>
					<h2 class="label">${e.phase??"Moon"}</h2>
					${e.date?s`<div class="date">${e.date}</div>`:l}
				</div>
			</div>
			<div class="stats">
				${typeof e.illumination=="number"?s`<div>
							<span>Illumination</span>
							<strong>${la(e.illumination)}</strong>
						</div>`:l}
				${typeof e.age=="number"?s`<div>
							<span>Age</span>
							<strong>${C(e.age,1)} days</strong>
						</div>`:l}
				${e.sign?s`<div>
							<span>Sign</span>
							<strong>${e.sign}</strong>
						</div>`:l}
				${typeof e.distance=="number"?s`<div>
							<span>Distance</span>
							<strong>${(e.distance/1e3).toFixed(0)}k km</strong>
						</div>`:l}
			</div>
			${e.meaning?.description?s`<p class="meaning">${e.meaning.description}</p>`:l}
			${e.meaning?.keywords?.length?s`<div class="keywords">
						${e.meaning.keywords.map(r=>s`<span>${r}</span>`)}
					</div>`:l}
		</article>`}renderListItem(e){let t=cr(e.phase);return s`<div class="list-item" role="listitem">
			<span aria-hidden="true">${t}</span>
			<span>${e.phase}</span>
			<span>${e.date??""}</span>
		</div>`}};ae.styles=[v,f`
			.card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.hero {
				display: flex;
				align-items: center;
				gap: var(--roxy-space-md, 1rem);
			}
			.emoji {
				font-size: 3rem;
				line-height: 1;
			}
			.label {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: capitalize;
			}
			.date {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}

			.stats {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr));
				gap: var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-secondary, #475569);
			}
			.stats div span:first-child {
				display: block;
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-xs, 0.75rem);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.stats strong {
				color: var(--roxy-fg, #0a0a0a);
				font-variant-numeric: tabular-nums;
			}

			.meaning {
				color: var(--roxy-fg, #0a0a0a);
			}
			.keywords {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
				margin-top: var(--roxy-space-sm, 0.5rem);
			}
			.keywords span {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 14%, transparent);
				padding: 2px 8px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
			}

			.list {
				display: grid;
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.list-item {
				display: grid;
				grid-template-columns: 2.5rem 1fr auto;
				gap: var(--roxy-space-sm, 0.5rem);
				align-items: center;
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				padding: var(--roxy-space-sm, 0.5rem) 0;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.list-item:last-child {
				border-bottom: none;
			}
		`],p([u({attribute:!1})],ae.prototype,"data",2),p([u({type:String,reflect:!0})],ae.prototype,"mode",2),ae=p([b("roxy-moon-phase")],ae);function cr(n){return n?tr[n.toLowerCase()]??"\u{1F319}":"\u{1F319}"}function la(n){let a=n<=1?n*100:n;return`${Math.round(a)}%`}var ye=class extends x{constructor(){super();this.data=null;new $(this)}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No nakshatra data</div>`;let e=this.data,t=e.remedies;return s`<article class="wrap" aria-label=${`Nakshatra ${e.name}`}>
			<header class="head">
				<h2 class="name">${e.name}</h2>
				${typeof e.number=="number"?s`<span class="number">Nakshatra ${e.number} of 27</span>`:l}
				${e.range?s`<span class="range">${e.range}</span>`:l}
			</header>

			<dl class="facts">
				${e.lord?s`<div class="fact"><dt>Lord</dt><dd>${e.lord}</dd></div>`:l}
				${e.deity?s`<div class="fact"><dt>Deity</dt><dd>${e.deity}</dd></div>`:l}
				${e.symbol?s`<div class="fact"><dt>Symbol</dt><dd>${e.symbol}</dd></div>`:l}
			</dl>

			${e.characteristics?s`<div class="section">
						<h3>Characteristics</h3>
						<p>${e.characteristics}</p>
					</div>`:l}

			${t?s`<div class="section">
						<h3>Remedies</h3>
						<div class="remedies">
							${t.mantras?s`<div class="remedy"><strong>Mantras:</strong> ${t.mantras}</div>`:l}
							${t.gemstones?s`<div class="remedy"><strong>Gemstones:</strong> ${t.gemstones}</div>`:l}
							${t.rituals?s`<div class="remedy"><strong>Rituals:</strong> ${t.rituals}</div>`:l}
						</div>
					</div>`:l}
		</article>`}};ye.styles=[v,f`
			.wrap {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				background: var(--roxy-bg, #fff);
				padding: var(--roxy-space-md, 1rem);
				display: grid;
				gap: var(--roxy-space-md, 1rem);
				box-shadow: var(--roxy-shadow-sm);
			}
			.head {
				display: flex;
				align-items: baseline;
				gap: var(--roxy-space-sm, 0.5rem);
				flex-wrap: wrap;
			}
			.name {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.number {
				color: var(--roxy-accent-fg, #b45309);
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.range {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.facts {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr));
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.fact {
				display: grid;
				gap: 2px;
			}
			.fact dt {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-xs, 0.75rem);
				text-transform: uppercase;
				letter-spacing: 0.05em;
			}
			.fact dd {
				margin: 0;
				color: var(--roxy-fg, #0a0a0a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.section h3 {
				margin: 0 0 var(--roxy-space-xs, 0.25rem);
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.05em;
			}
			.section p {
				margin: 0;
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-fg, #0a0a0a);
				line-height: 1.5;
			}
			.remedies {
				display: grid;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.remedy {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-fg, #0a0a0a);
			}
			.remedy strong {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
			}
		`],p([u({attribute:!1})],ye.prototype,"data",2),ye=p([b("roxy-nakshatra-card")],ye);var Rt=420,A=Rt/2,Ne=164,Tt=146,Ye=120,ze=96,da=178,ca=196,q=class extends x{constructor(){super();this.data=null;this.houseSystem="placidus";this.view="wheel";new $(this)}getPlanets(){return this.data?.planets??[]}getAscendant(){return this.data?.ascendant?.longitude??0}getMidheaven(){let e=this.data?.midheaven?.longitude;return typeof e=="number"?e:null}toAngle(e){return 180+this.getAscendant()-e}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No chart data</div>`;let e=this.getPlanets(),t=this.data.aspects??[],r=this.view;return s`<div class="wrap">
			<header>
				<h2 class="title">Natal chart</h2>
				${this.data.birthDetails?s`<div class="meta">
							${[this.data.birthDetails.date,this.data.birthDetails.time].filter(Boolean).join(" \xB7 ")}
						</div>`:l}
			</header>
			<div
				class="tablist"
				role="tablist"
				aria-label="Natal chart views"
				@keydown=${this.onTabKeyDown}
			>
				${["wheel","grid"].map(o=>s`<button
						class="tab"
						role="tab"
						id="tab-${o}"
						aria-selected=${r===o?"true":"false"}
						aria-controls="panel-${o}"
						tabindex=${r===o?"0":"-1"}
						@click=${()=>{this.view=o}}
					>
						${o==="wheel"?"Wheel":"Aspect grid"}
					</button>`)}
			</div>
			<div id="panel-${r}" role="tabpanel" aria-labelledby="tab-${r}">
				${r==="wheel"?this.renderWheel(e,t):this.renderAspectGrid(e,t)}
			</div>
			<div class="legend">
				<span>${e.length} planets</span>
				<span>${t.length} aspects</span>
				${this.data.houseSystem?s`<span>${this.data.houseSystem} houses</span>`:l}
				<span><span class="legend-swatch" style="background: var(--roxy-success)"></span>harmonious</span>
				<span><span class="legend-swatch" style="background: var(--roxy-danger)"></span>challenging</span>
			</div>
			${this.renderDetails()}
			${this.renderInterpretations()}
		</div>`}onTabKeyDown(e){if(e.key!=="ArrowRight"&&e.key!=="ArrowLeft")return;e.preventDefault(),this.view=this.view==="wheel"?"grid":"wheel";let t=this.view;requestAnimationFrame(()=>{this.shadowRoot?.querySelector(`#tab-${t}`)?.focus()})}renderWheel(e,t){return s`<svg
			viewBox="0 0 ${Rt} ${Rt}"
			role="img"
			aria-label="Natal chart wheel with twelve houses, planets, and aspects"
		>
			<title>Natal chart wheel</title>
			<desc>
				Twelve zodiac sign segments around a circular wheel. Planet glyphs are
				placed at their ecliptic longitudes. Aspect lines connect related planets.
			</desc>
			<circle class="wheel-line" cx=${A} cy=${A} r=${Ne} stroke-width="1.5" />
			<circle class="wheel-line" cx=${A} cy=${A} r=${Tt-14} stroke-width="0.8" />
			<circle class="wheel-line" cx=${A} cy=${A} r=${Ye} stroke-width="1" />
			<circle class="wheel-line" cx=${A} cy=${A} r=${ze-16} stroke-width="0.5" />
			${this.renderTicks()} ${this.renderSpokes()} ${this.renderSigns()}
			${this.renderHouseNumbers()} ${this.renderCuspDegrees()}
			${this.renderAspects(e,t)} ${this.renderPlanets(e)}
			${this.renderAngles()}
		</svg>`}renderAspectGrid(e,t){let r=e.map(i=>E(i.name)),o=new Map;for(let i of t){let d=[E(i.planet1),E(i.planet2)].sort().join("|");o.set(d,i)}return r.length===0?s`<p class="roxy-empty" role="status">No planets to grid</p>`:s`<div class="grid-scroll">
			<table class="aspect-grid" aria-label="Planet by planet aspect grid">
				<thead>
					<tr>
						<th></th>
						${r.slice(0,-1).map(i=>{let d=M[i]??i.slice(0,2);return s`<th scope="col" title=${i}>${d}</th>`})}
					</tr>
				</thead>
				<tbody>
					${r.slice(1).map((i,d)=>{let c=M[i]??i.slice(0,2);return s`<tr>
							<th scope="row" title=${i}>${c}</th>
							${r.slice(0,d+1).map(m=>{let g=o.get([i,m].sort().join("|"));if(!g)return s`<td class="empty"></td>`;let h=Me(g),y=St[h]??St[h.replace(/-/g,"")]??h.slice(0,3),S=Ve[h]??"aspect-other",w=C(g.orb,1);return s`<td class=${`cell ${S}`} title=${`${i} ${h} ${m}${w?` (orb ${w}\xB0)`:""}`}>
									<span class="asp">${y}</span>
								</td>`})}
							${r.slice(d+1,-1).map(()=>s`<td class="empty"></td>`)}
						</tr>`})}
				</tbody>
			</table>
		</div>`}renderAngles(){let e=this.getAscendant(),t=this.getMidheaven(),r=[this.renderAngleMark(e,"ASC"),this.renderAngleMark(Et(e),"DSC")];t!==null&&(r.push(this.renderAngleMark(t,"MC")),r.push(this.renderAngleMark(Et(t),"IC")));let o=this.data?.partOfFortune?.longitude;typeof o=="number"&&r.push(this.renderAngleMark(B(o),"PoF"));let i=this.data?.vertex?.longitude;return typeof i=="number"&&r.push(this.renderAngleMark(B(i),"Vtx")),r}renderAngleMark(e,t){let r=this.toAngle(e),o=P(A,A,Ne,r),i=P(A,A,da,r),d=P(A,A,ca,r);return k`
			<g>
				<line class="angle-tick" x1=${o.x} y1=${o.y} x2=${i.x} y2=${i.y} />
				<text class="angle-marker" x=${d.x} y=${d.y} text-anchor="middle" dominant-baseline="central">${t}</text>
			</g>
		`}renderSpokes(){let e=this.data?.houses??[];return(e.length===12?e.map(r=>r.longitude):Array.from({length:12},(r,o)=>this.getAscendant()+o*30)).map(r=>{let o=this.toAngle(r),i=P(A,A,Ye,o),d=P(A,A,Ne,o);return k`<line class="wheel-line" x1=${i.x} y1=${i.y} x2=${d.x} y2=${d.y} stroke-width="0.8" />`})}renderSigns(){return _.map((e,t)=>{let r=this.toAngle(t*30+15),o=P(A,A,Tt,r);return k`<text class="sign-glyph" x=${o.x} y=${o.y} text-anchor="middle" dominant-baseline="central">${G[e]}</text>`})}renderHouseNumbers(){let e=this.data?.houses??[];if(e.length===12)return e.map((r,o)=>{let i=e[(o+1)%12],d=nr(r.longitude,i?i.longitude:r.longitude+30),c=P(A,A,Ye-12,this.toAngle(d));return k`<text class="house-num" x=${c.x} y=${c.y} text-anchor="middle" dominant-baseline="central">${r.number}</text>`});let t=Math.floor(this.getAscendant()/30);return Array.from({length:12},(r,o)=>{let i=this.toAngle(o*30+15),d=P(A,A,Ye-12,i),c=(o-t+12)%12+1;return k`<text class="house-num" x=${d.x} y=${d.y} text-anchor="middle" dominant-baseline="central">${c}</text>`})}renderTicks(){let e=[];for(let t=0;t<360;t+=5){let r=this.toAngle(t),o=t%30===0,i=o?Tt-14:Ne-5,d=P(A,A,i,r),c=P(A,A,Ne,r);e.push(k`<line class=${o?"tick tick-major":"tick"} x1=${d.x} y1=${d.y} x2=${c.x} y2=${c.y} stroke-width=${o?1:.5} />`)}return e}renderCuspDegrees(){let e=this.data?.houses??[];return e.length!==12?l:e.map(t=>{let r=this.toAngle(t.longitude),o=P(A,A,Ye+9,r),i=K(t.longitude);return k`<text class="cusp-deg" x=${o.x} y=${o.y} text-anchor="middle" dominant-baseline="central">${i.degree}°${String(i.minute).padStart(2,"0")}'</text>`})}renderPlanets(e){let r=e.filter(i=>Number.isFinite(i.longitude)).map(i=>({p:i,trueLon:B(i.longitude),displayLon:B(i.longitude)})).sort((i,d)=>i.trueLon-d.trueLon);for(let i=1;i<r.length;i++){let d=r[i-1],c=r[i];if(!d||!c)continue;let m=d.displayLon+7;c.displayLon<m&&(c.displayLon=m)}let o=r[r.length-1];if(o&&o.displayLon>360){let i=o.displayLon-360;for(let d of r)d.displayLon-=i}return r.map(({p:i,trueLon:d,displayLon:c})=>{let m=this.toAngle(d),g=this.toAngle(c),h=P(A,A,ze,g),y=P(A,A,ze-13,g),S=P(A,A,Ne-4,m),w=P(A,A,ze+8,g),we=M[E(i.name)]??i.name.slice(0,2),Ue=K(i.longitude),De=i.isRetrograde===!0,Fe=`${Ue.degree}\xB0${String(Ue.minute).padStart(2,"0")}'`,We=Math.abs(c-d)>.5;return k`<g>
				${We?k`<line class="planet-leader" x1=${S.x} y1=${S.y} x2=${w.x} y2=${w.y} />`:l}
				<text class="planet-glyph" x=${h.x} y=${h.y} text-anchor="middle" dominant-baseline="central"><title>${i.name}${De?" retrograde":""} - ${Fe} ${i.sign??""}</title>${we}</text>
				<text class="planet-deg" x=${y.x} y=${y.y} text-anchor="middle" dominant-baseline="central">${Fe}${De?k`<tspan class="retro"> ℞</tspan>`:l}</text>
			</g>`})}renderDetails(){let e=this.data?.summary,t=this.data?.aspectsInterpretation;if(!e&&!t)return l;let r=e?.retrogradePlanets??[];return s`<div class="details">
			${e?.dominantElement||e?.dominantModality?s`<div class="pill-row">
						${e.dominantElement?s`<span class="pill">Dominant element: ${e.dominantElement}</span>`:l}
						${e.dominantModality?s`<span class="pill">Dominant modality: ${e.dominantModality}</span>`:l}
					</div>`:l}
			${t?s`<div class="pill-row">
						<span class="pill pill--success">Harmonious ${t.harmonious}</span>
						<span class="pill pill--danger">Challenging ${t.challenging}</span>
						<span class="pill pill--muted">Neutral ${t.neutral}</span>
					</div>`:l}
			${r.length>0?s`<div class="pill-row">
						${r.map(o=>{let i=M[o]??o.slice(0,2);return s`<span class="pill pill--muted">${i} ${o} R</span>`})}
					</div>`:l}
			${t?.summary?s`<p class="summary">${t.summary}</p>`:l}
			${this.renderElementModalityGrid()}
		</div>`}renderElementModalityGrid(){let e=this.getPlanets();if(e.length===0)return l;let t=["Fire","Earth","Air","Water"],r=["Cardinal","Fixed","Mutable"],o=_,i={};for(let d of t)i[d]={Cardinal:[],Fixed:[],Mutable:[]};for(let d of e){let c=o.indexOf(E(d.sign??""));if(c<0)continue;let m=t[c%4],g=r[c%3],h=M[E(d.name)]??E(d.name).slice(0,2);i[m]?.[g]?.push(h)}return s`<table class="em-grid" aria-label="Element and modality distribution">
			<thead>
				<tr>
					<th></th>
					${r.map(d=>s`<th scope="col">${d.slice(0,3)}</th>`)}
					<th scope="col">Total</th>
				</tr>
			</thead>
			<tbody>
				${t.map(d=>{let c=r.reduce((m,g)=>m+(i[d]?.[g]?.length??0),0);return s`<tr>
						<th scope="row">${d}</th>
						${r.map(m=>s`<td>${(i[d]?.[m]??[]).join(" ")}</td>`)}
						<td class="em-total">${c}</td>
					</tr>`})}
				<tr>
					<th scope="row">Total</th>
					${r.map(d=>s`<td class="em-total">${t.reduce((c,m)=>c+(i[m]?.[d]?.length??0),0)}</td>`)}
					<td class="em-total">${e.length}</td>
				</tr>
			</tbody>
		</table>`}renderInterpretations(){let e=this.getPlanets().filter(t=>t.interpretation);return e.length===0?l:s`<section class="interpretations">
			<h3>Planet readings</h3>
			${e.map((t,r)=>{let o=t.interpretation,i=M[E(t.name)]??"",d=C(t.degree??0,1);return s`<details class="interp-card" name="natal-planet-readings" ?open=${r===0}>
					<summary>${i} ${t.name} <small>${t.sign??""} ${d}</small></summary>
					<div class="interp-body">
						${o.summary?s`<p class="interp-summary">${o.summary}</p>`:l}
						${o.detailed?s`<p class="interp-detail">${o.detailed}</p>`:l}
						${o.keywords?.length?s`<div class="interp-keywords">${o.keywords.map(c=>s`<span class="kw">${c}</span>`)}</div>`:l}
					</div>
				</details>`})}
		</section>`}renderAspects(e,t){let r=new Map;for(let o of e){if(typeof o.longitude!="number")continue;let i=E(o.name);i&&r.set(i,o.longitude)}return t.map(o=>{let i=r.get(E(o.planet1)),d=r.get(E(o.planet2));if(i===void 0||d===void 0)return l;let c=P(A,A,ze-18,this.toAngle(i)),m=P(A,A,ze-18,this.toAngle(d)),g=Me(o),h=Ve[g]??"aspect-other",y=C(o.orb,1);return k`<line class=${`aspect ${h}`} x1=${c.x} y1=${c.y} x2=${m.x} y2=${m.y}><title>${o.planet1} ${g||""} ${o.planet2}${y?` (orb ${y}\xB0)`:""}</title></line>`})}};q.styles=[v,f`
			.wrap {
				width: 100%;
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.title {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
				color: var(--roxy-primary, #0f172a);
			}

			.meta {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}

			svg {
				display: block;
				width: 100%;
				max-width: 560px;
				aspect-ratio: 1 / 1;
				height: auto;
				margin: 0 auto;
			}

			.wheel-line {
				fill: none;
				stroke: var(--roxy-border, #e4e4e7);
			}

			.sign-glyph {
				fill: var(--roxy-secondary, #475569);
				font-size: 14px;
				font-family: var(--roxy-font-sans);
			}

			.planet-glyph {
				fill: var(--roxy-accent, #f59e0b);
				font-size: 14px;
				font-weight: 600;
				font-family: var(--roxy-font-sans);
			}

			.planet-deg {
				fill: var(--roxy-fg, #0a0a0a);
				font-size: 7px;
				font-family: var(--roxy-font-sans);
			}

			/* Below 480px the chart container shrinks to ~320px on phones.
			 * Bump in-SVG text up proportionally so the 7px degree band
			 * does not collapse below ~6px on screen.
			 */
			@container (max-width: 480px) {
				.sign-glyph,
				.planet-glyph {
					font-size: 18px;
				}
				.planet-deg {
					font-size: 10px;
				}
				.house-num {
					font-size: 12px;
				}
			}

			.planet-deg .retro {
				fill: var(--roxy-danger, #dc2626);
			}

			.planet-leader {
				stroke: var(--roxy-accent, #f59e0b);
				stroke-width: 0.5;
				opacity: 0.55;
			}

			.house-num {
				fill: var(--roxy-muted, #71717a);
				font-size: 9px;
				font-family: var(--roxy-font-sans);
			}

			.cusp-deg {
				fill: var(--roxy-muted, #71717a);
				font-size: 6px;
				font-family: var(--roxy-font-sans);
			}

			.tick {
				stroke: var(--roxy-border, #e4e4e7);
			}
			.tick-major {
				stroke: var(--roxy-secondary, #475569);
			}

			.aspect {
				stroke-width: 0.8;
				fill: none;
				opacity: 0.55;
			}
			.aspect-trine,
			.aspect-sextile {
				stroke: var(--roxy-success, #16a34a);
			}
			.aspect-square,
			.aspect-opposition {
				stroke: var(--roxy-danger, #dc2626);
			}
			.aspect-conjunction {
				stroke: var(--roxy-accent-fg, #b45309);
			}
			.aspect-other {
				stroke: var(--roxy-muted, #71717a);
				opacity: 0.4;
			}

			.angle-marker {
				fill: var(--roxy-accent-fg, #b45309);
				font-size: 10px;
				font-weight: 700;
				font-family: var(--roxy-font-sans);
				letter-spacing: 0.04em;
			}
			.angle-tick {
				stroke: var(--roxy-accent-fg, #b45309);
				stroke-width: 1.5;
			}

			.legend {
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-md, 1rem);
			}
			.legend-swatch {
				display: inline-block;
				width: 8px;
				height: 8px;
				border-radius: 50%;
				margin-right: 4px;
				vertical-align: middle;
			}

			.tablist {
				display: flex;
				gap: 2px;
				border-bottom: 2px solid var(--roxy-border, #e4e4e7);
			}
			.tab {
				padding: var(--roxy-space-xs, 0.25rem) var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-sm, 0.875rem);
				background: none;
				border: none;
				border-bottom: 2px solid transparent;
				margin-bottom: -2px;
				cursor: pointer;
				color: var(--roxy-muted, #71717a);
				font-family: inherit;
				transition: color var(--roxy-motion-duration, 200ms) var(--roxy-motion-easing, ease);
			}
			.tab[aria-selected='true'] {
				color: var(--roxy-accent-fg, #b45309);
				border-bottom-color: var(--roxy-accent, #f59e0b);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.tab:hover:not([aria-selected='true']) {
				color: var(--roxy-fg, #0a0a0a);
			}

			.grid-scroll {
				overflow-x: auto;
				-webkit-overflow-scrolling: touch;
			}
			table.aspect-grid {
				border-collapse: collapse;
				font-size: var(--roxy-text-xs, 0.75rem);
				margin: 0 auto;
			}
			table.aspect-grid th,
			table.aspect-grid td {
				width: 1.6rem;
				height: 1.6rem;
				text-align: center;
				border: 1px solid var(--roxy-border, #e4e4e7);
				padding: 0;
			}
			table.aspect-grid th {
				color: var(--roxy-secondary, #475569);
				font-weight: var(--roxy-weight-bold, 600);
			}
			table.aspect-grid td.cell {
				cursor: default;
			}
			table.aspect-grid td.empty {
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 18%, transparent);
			}
			table.aspect-grid td .asp {
				font-size: 0.95em;
				line-height: 1;
			}
			table.aspect-grid td.aspect-trine .asp,
			table.aspect-grid td.aspect-sextile .asp {
				color: var(--roxy-success, #16a34a);
			}
			table.aspect-grid td.aspect-square .asp,
			table.aspect-grid td.aspect-opposition .asp {
				color: var(--roxy-danger, #dc2626);
			}
			table.aspect-grid td.aspect-conjunction .asp {
				color: var(--roxy-accent-fg, #b45309);
			}
			table.aspect-grid td.aspect-other .asp {
				color: var(--roxy-muted, #71717a);
			}

			.details {
				margin-top: var(--roxy-space-md, 1rem);
			}

			.pill-row {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
				margin-bottom: var(--roxy-space-xs, 0.25rem);
			}

			.pill {
				padding: 2px 8px;
				border-radius: var(--roxy-radius-sm, 4px);
				font-size: var(--roxy-text-xs, 0.75rem);
				background: color-mix(in srgb, var(--roxy-fg, #0f172a) 8%, transparent);
				color: var(--roxy-fg, #0f172a);
			}

			.pill--success {
				background: color-mix(in srgb, var(--roxy-success, #16a34a) 15%, transparent);
				color: var(--roxy-success, #16a34a);
			}

			.pill--danger {
				background: color-mix(in srgb, var(--roxy-danger, #dc2626) 15%, transparent);
				color: var(--roxy-danger, #dc2626);
			}

			.pill--muted {
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 60%, transparent);
				color: var(--roxy-fg, #0a0a0a);
			}

			.summary {
				color: var(--roxy-fg, #0f172a);
				font-size: var(--roxy-text-sm, 0.875rem);
				margin: var(--roxy-space-md, 1rem) 0;
			}

			.em-grid {
				border-collapse: collapse;
				font-size: var(--roxy-text-xs, 0.75rem);
				width: 100%;
			}
			.em-grid th,
			.em-grid td {
				border: 1px solid var(--roxy-border, #e4e4e7);
				padding: 3px 5px;
				text-align: center;
				vertical-align: middle;
			}
			.em-grid th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				letter-spacing: 0.04em;
			}
			.em-grid th[scope='row'] {
				text-align: left;
			}
			.em-grid td {
				color: var(--roxy-accent, #f59e0b);
				font-size: 0.95em;
				line-height: 1.4;
				min-width: 1.4rem;
			}
			.em-grid .em-total {
				color: var(--roxy-fg, #0a0a0a);
				font-weight: var(--roxy-weight-bold, 600);
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 25%, transparent);
			}

			.interpretations {
				margin-top: var(--roxy-space-md, 1rem);
			}
			.interpretations h3 {
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: 600;
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.06em;
				margin: 0 0 var(--roxy-space-sm, 0.5rem);
			}
			.interp-card {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				margin-bottom: var(--roxy-space-xs, 0.25rem);
			}
			.interp-card summary {
				cursor: pointer;
				font-weight: 500;
				color: var(--roxy-fg, #0f172a);
			}
			.interp-card summary small {
				color: var(--roxy-muted, #71717a);
				margin-left: 0.5em;
				font-weight: 400;
			}
			.interp-body {
				margin-top: var(--roxy-space-xs, 0.25rem);
				color: var(--roxy-fg, #0f172a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.interp-keywords {
				display: flex;
				flex-wrap: wrap;
				gap: 0.25rem;
				margin-top: 0.5rem;
			}
			.interp-keywords .kw {
				padding: 1px 8px;
				border-radius: 9999px;
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 14%, transparent);
				color: var(--roxy-accent-fg, #b45309);
				font-size: var(--roxy-text-xs, 0.75rem);
			}
		`],p([u({attribute:!1})],q.prototype,"data",2),p([u({type:String,attribute:"house-system",reflect:!0})],q.prototype,"houseSystem",2),p([z()],q.prototype,"view",2),q=p([b("roxy-natal-chart")],q);var se=class extends x{constructor(){super();this.data=null;this.type="life-path";new $(this)}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No numerology data</div>`;let t=pa[this.type]??this.type;return"coreNumbers"in e?this.renderChart(e,t):"personalYear"in e?this.renderPersonalYear(e,t):this.renderNumberCard(e,t)}renderNumberCard(e,t){let r=e.meaning?.keywords??[];return s`<article class="card" aria-label=${t}>
			<div class="hero">
				${typeof e.number=="number"?s`<div class="numeral">${e.number}</div>`:l}
				<div>
					<p class="label">${t}</p>
					${e.meaning?.title?s`<h2 class="title">${e.meaning.title}</h2>`:l}
				</div>
			</div>
			${e.meaning?.description?s`<p class="meaning">${e.meaning.description}</p>`:l}
			${e.calculation?s`<pre class="calc">${e.calculation}</pre>`:l}
			${r.length>0?s`<div class="chips">
						${r.map(o=>s`<span>${o}</span>`)}
					</div>`:l}
			${e.hasKarmicDebt&&e.karmicDebtNumber?s`<div class="karmic">
						Karmic debt ${e.karmicDebtNumber}.
						${ma(e.karmicDebtMeaning)}
					</div>`:l}
		</article>`}renderPersonalYear(e,t){return s`<article class="card" aria-label=${t}>
			<div class="hero">
				${typeof e.personalYear=="number"?s`<div class="numeral">${e.personalYear}</div>`:l}
				<div>
					<p class="label">${t}</p>
					${e.theme?s`<h2 class="title">${e.theme}</h2>`:l}
				</div>
			</div>
			${e.forecast?s`<p class="meaning">${e.forecast}</p>`:l}
			${e.advice?s`<p>${e.advice}</p>`:l}
		</article>`}renderChart(e,t){let r=Object.entries(e.coreNumbers).filter(([,o])=>o!=null);return s`<article class="card" aria-label=${t}>
			<div>
				<p class="label">${t}</p>
				${e.profile?.name?s`<h2 class="title">${e.profile.name}</h2>`:l}
			</div>
			${r.length>0?s`<div class="cores">
						${r.map(([o,i])=>s`<div class="item">
								<span>${pe(o)}</span>
								<strong>${i.number??""}</strong>
							</div>`)}
					</div>`:l}
		</article>`}};se.styles=[v,f`
			.card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.hero {
				display: flex;
				align-items: center;
				gap: var(--roxy-space-md, 1rem);
			}
			.numeral {
				font-size: 4rem;
				line-height: 1;
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-accent-fg, #b45309);
				font-variant-numeric: tabular-nums;
			}
			.label {
				margin: 0;
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.meaning {
				margin: 0;
				color: var(--roxy-fg, #0a0a0a);
			}

			.calc {
				margin: 0;
				font-family: var(--roxy-font-mono);
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 30%, transparent);
				padding: var(--roxy-space-sm, 0.5rem);
				border-radius: var(--roxy-radius-sm, 4px);
				white-space: pre-wrap;
				overflow-wrap: anywhere;
			}

			.chips {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.chips span {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 14%, transparent);
				padding: 2px 8px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
			}

			.cores {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr));
				gap: var(--roxy-space-sm, 0.5rem);
				border-top: 1px solid var(--roxy-border, #e4e4e7);
				padding-top: var(--roxy-space-md, 1rem);
			}
			.cores .item {
				display: flex;
				align-items: baseline;
				gap: var(--roxy-space-xs, 0.25rem);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			.cores .item span:first-child {
				color: var(--roxy-muted, #71717a);
				text-transform: capitalize;
			}
			.cores .item strong {
				color: var(--roxy-accent-fg, #b45309);
				font-variant-numeric: tabular-nums;
				font-size: var(--roxy-text-base, 1rem);
				font-weight: var(--roxy-weight-bold, 600);
			}

			.karmic {
				background: color-mix(in srgb, var(--roxy-warning, #ea580c) 12%, transparent);
				border: 1px solid color-mix(in srgb, var(--roxy-warning, #ea580c) 32%, transparent);
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				border-radius: var(--roxy-radius-md, 8px);
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-fg, #0a0a0a);
			}
		`],p([u({attribute:!1})],se.prototype,"data",2),p([u({type:String,reflect:!0})],se.prototype,"type",2),se=p([b("roxy-numerology-card")],se);var pa={"life-path":"Life Path",expression:"Expression","personal-year":"Personal Year",chart:"Numerology chart"};function ma(n){return n?[n.description,n.challenge,n.resolution].filter(Boolean).join(" "):""}var oe=class extends x{constructor(){super();this.data=null;this.detail="detailed";new $(this)}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No panchang data</div>`;let t="sunrise"in e?e:null,r=[["Tithi",this.formatPart(e.tithi)],["Nakshatra",this.formatPart(e.nakshatra)],["Yoga",this.formatPart(e.yoga)],["Karana",this.formatPart(e.karana)]];t&&r.push(["Vara",this.formatPart(t.vara)]);let o=t?[["Brahma Muhurta",t.brahmaMuhurta],["Abhijit Muhurta",t.abhijitMuhurta],["Vijaya Muhurta",t.vijayaMuhurta],["Godhuli Muhurta",t.godhuliMuhurta],["Nishita Muhurta",t.nishitaMuhurta],["Pratah Sandhya",t.pratahSandhya],["Sayahna Sandhya",t.sayahnaSandhya]]:[],i=t?[["Rahu Kaal",t.rahuKaal],["Yamaganda",t.yamaganda],["Gulika",t.gulika]]:[],d=t&&"transitions"in t?t.transitions:void 0;return s`<div class="wrap" aria-label="Panchang">
			<header class="head">
				<h2 class="title">Panchang</h2>
				<span class="date">${t?at(t.date):""}</span>
			</header>
			<table>
				<tbody>
					${r.map(([c,m])=>s`<tr>
							<th>${c}</th>
							<td>${m}</td>
						</tr>`)}
					${t?.sunrise?s`<tr>
								<th>Sunrise</th>
								<td>${I(t.sunrise)}</td>
							</tr>`:l}
					${t?.sunset?s`<tr>
								<th>Sunset</th>
								<td>${I(t.sunset)}</td>
							</tr>`:l}
					${t?.moonrise?s`<tr>
								<th>Moonrise</th>
								<td>${I(t.moonrise)}</td>
							</tr>`:l}
					${t?.moonset?s`<tr>
								<th>Moonset</th>
								<td>${I(t.moonset)}</td>
							</tr>`:l}
				</tbody>
			</table>
			${d?s`
						<div class="section">Next transitions</div>
						<table>
							<tbody>
								${this.renderTransitionRow("Tithi",d.tithi)}
								${this.renderTransitionRow("Nakshatra",d.nakshatra)}
								${this.renderTransitionRow("Yoga",d.yoga)}
								${this.renderTransitionRow("Karana",d.karana)}
							</tbody>
						</table>
					`:l}
			${this.detail==="detailed"&&(o.some(c=>!!c[1])||i.some(c=>!!c[1]))?s`
						<div class="section">Auspicious muhurtas</div>
						<table>
							<tbody>
								${o.filter(([,c])=>!!c).map(([c,m])=>s`<tr>
											<th>${c}</th>
											<td>${Ct(m)}</td>
										</tr>`)}
							</tbody>
						</table>
						<div class="section">Inauspicious periods</div>
						<table>
							<tbody>
								${i.filter(([,c])=>!!c).map(([c,m])=>s`<tr>
											<th>${c}</th>
											<td>${Ct(m)}</td>
										</tr>`)}
							</tbody>
						</table>
					`:l}
		</div>`}renderTransitionRow(e,t){if(!t?.endsAt)return l;let r=I(t.endsAt),o=t.next?` \u2192 ${t.next}`:"";return s`<tr>
			<th>${e}</th>
			<td>ends ${r}${o}</td>
		</tr>`}formatPart(e){if(!e)return"";if(typeof e=="string")return e;if(typeof e=="object"){let t=e;return[t.name,t.paksha?`(${t.paksha} paksha)`:"",t.lord?`\xB7 ${t.lord}`:"",t.phase].filter(Boolean).join(" ")}return String(e)}};oe.styles=[v,f`
			.wrap {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				background: var(--roxy-bg, #fff);
				overflow: hidden;
				box-shadow: var(--roxy-shadow-sm);
			}
			.head {
				padding: var(--roxy-space-md, 1rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				display: flex;
				justify-content: space-between;
				flex-wrap: wrap;
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.date {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			tbody tr:nth-child(odd) {
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 24%, transparent);
			}
			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				text-align: left;
				vertical-align: top;
			}
			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				width: 38%;
				text-transform: capitalize;
			}
			td {
				color: var(--roxy-fg, #0a0a0a);
				font-variant-numeric: tabular-nums;
			}
			.section {
				border-top: 1px solid var(--roxy-border, #e4e4e7);
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
		`],p([u({attribute:!1})],oe.prototype,"data",2),p([u({type:String,reflect:!0})],oe.prototype,"detail",2),oe=p([b("roxy-panchang-table")],oe);var Mt=[{key:"sthanaBala",label:"Sthana",color:"var(--roxy-info, #0284c7)"},{key:"digBala",label:"Dig",color:"var(--roxy-success, #16a34a)"},{key:"kalaBala",label:"Kala",color:"var(--roxy-warning, #ea580c)"},{key:"chestaBala",label:"Chesta",color:"var(--roxy-accent, #f59e0b)"},{key:"naisargikaBala",label:"Naisargika",color:"var(--roxy-secondary, #475569)"},{key:"drikBala",label:"Drik",color:"var(--roxy-danger, #dc2626)"}],xe=class extends x{constructor(){super();this.data=null;new $(this)}render(){if(!this.data?.planets?.length)return s`<div class="roxy-empty" role="status">No shadbala data</div>`;let e=[...this.data.planets].sort((t,r)=>t.relativeRank-r.relativeRank);return s`<div class="wrap" aria-label="Shadbala planetary strength">
			<div class="head">
				<h2 class="title">Shadbala</h2>
				<p class="subtitle">${e.length} planets ranked by strength</p>
			</div>

			<div role="list" aria-label="Planet strength bars">
				${e.map(t=>this.renderPlanetRow(t))}
			</div>

			<div class="legend" aria-label="Strength component legend">
				${Mt.map(t=>s`<div class="legend-row">
						<span
							class="legend-swatch"
							style="background: ${t.color}"
							aria-hidden="true"
						></span>
						${t.label}
					</div>`)}
			</div>
		</div>`}renderPlanetRow(e){let t=M[E(e.planet)]??"",r=Mt.map(g=>Math.max(0,e[g.key])),o=r.reduce((g,h)=>g+h,0),i=typeof e.strengthRatio=="number"&&e.strengthRatio>=1,d=i?"adequacy-badge--adequate":"adequacy-badge--weak",c=i?"adequate":"weak",m=C(e.totalRupas,2)&&C(e.minRequired,2)?`${C(e.totalRupas,2)} / ${C(e.minRequired,2)} R`:"";return s`<div class="planet-row" role="listitem" aria-label="${e.planet} shadbala">
			<div class="planet-label">
				<span class="glyph" aria-hidden="true">${t}</span>
				${e.planet}
				<span class="rank-badge" aria-label="rank ${e.relativeRank}">#${e.relativeRank}</span>
			</div>
			<div class="bar-wrap">
				<div class="bar" role="img" aria-label="Strength components for ${e.planet}">
					${o>0?Mt.map((g,h)=>{let y=r[h];if(y<=0)return l;let S=y/o*100;return s`<div
									class="bar-segment"
									style="flex-grow: ${S}; background: ${g.color};"
									title="${g.label}: ${C(y,1)}"
								></div>`}):l}
				</div>
			</div>
			<div class="pills">
				${m?s`<span class="rupas-label">${m}</span>`:l}
				<span class="${`adequacy-badge ${d}`}">${c}</span>
			</div>
		</div>`}};xe.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.head {
				display: flex;
				justify-content: space-between;
				align-items: baseline;
				gap: var(--roxy-space-md, 1rem);
				flex-wrap: wrap;
			}

			.title {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
			}

			.subtitle {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
				margin: 0;
			}

			.planet-row {
				display: grid;
				grid-template-columns: 8rem 1fr auto;
				align-items: center;
				gap: var(--roxy-space-sm, 0.5rem);
				padding: var(--roxy-space-sm, 0.5rem) 0;
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
			}

			.planet-row:last-of-type {
				border-bottom: none;
			}

			.planet-label {
				display: flex;
				align-items: center;
				gap: 6px;
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: var(--roxy-weight-bold, 600);
			}

			.glyph {
				font-size: 1.2em;
				line-height: 1;
			}

			.bar-wrap {
				display: flex;
				flex-direction: column;
				gap: 4px;
			}

			.bar {
				display: flex;
				height: 12px;
				border-radius: var(--roxy-radius-sm, 4px);
				overflow: hidden;
				background: var(--roxy-border, #e4e4e7);
			}

			.bar-segment {
				height: 100%;
				transition: flex-grow var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}

			.pills {
				display: flex;
				flex-direction: column;
				align-items: flex-end;
				gap: 4px;
			}

			.rupas-label {
				font-variant-numeric: tabular-nums;
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				white-space: nowrap;
			}

			.adequacy-badge {
				display: inline-block;
				padding: 1px 6px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: var(--roxy-weight-bold, 600);
			}

			.adequacy-badge--adequate {
				background: color-mix(in srgb, var(--roxy-success, #16a34a) 12%, transparent);
				color: var(--roxy-success-fg, #166534);
			}

			.adequacy-badge--weak {
				background: color-mix(in srgb, var(--roxy-danger, #dc2626) 12%, transparent);
				color: var(--roxy-danger-fg, #991b1b);
			}

			.rank-badge {
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-accent-fg, #b45309);
				font-weight: var(--roxy-weight-bold, 600);
			}

			.legend {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				border-top: 1px solid var(--roxy-border, #e4e4e7);
				padding-top: var(--roxy-space-sm, 0.5rem);
			}

			.legend-row {
				display: flex;
				align-items: center;
				gap: 6px;
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
			}

			.legend-swatch {
				display: inline-block;
				width: 10px;
				height: 10px;
				border-radius: var(--roxy-radius-sm, 4px);
				flex-shrink: 0;
			}

			@container (max-width: 480px) {
				.planet-row {
					grid-template-columns: 6rem 1fr;
					grid-template-rows: auto auto;
				}
				.pills {
					grid-column: 1 / -1;
					flex-direction: row;
					align-items: center;
					justify-content: flex-start;
				}
			}
		`],p([u({attribute:!1})],xe.prototype,"data",2),xe=p([b("roxy-shadbala-table")],xe);var _t=360,R=_t/2,Nt=170,ha=154,zt=124,_e=96,fe=class extends x{constructor(){super();this.data=null;new $(this)}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No synastry data</div>`;let{person1:e,person2:t,compatibilityScore:r,analysis:o}=this.data,i=this.data.interAspects??[],d=e?.planets??[],c=t?.planets??[],m=typeof r=="number"?Math.round(r):void 0,g=o?.overall,h=o?.strengths??[],y=o?.challenges??[];return d.length>0&&c.length>0?s`<div
			class="wrap"
			aria-label="Synastry compatibility chart"
		>
			<div class="head">
				<h2 class="title">Synastry</h2>
				${typeof m=="number"?s`<span class="score" aria-label=${`Score ${m} of 100`}
							>${m} / 100</span
						>`:l}
			</div>
			<svg
				viewBox="0 0 ${_t} ${_t}"
				role="img"
				aria-label="Dual chart wheel comparing two natal charts"
			>
				<title>Synastry dual wheel</title>
				<circle
					class="wheel-line"
					cx=${R}
					cy=${R}
					r=${Nt}
					stroke-width="1.5"
				/>
				<circle
					class="wheel-line"
					cx=${R}
					cy=${R}
					r=${_e+14}
					stroke-width="0.8"
				/>
				<circle
					class="wheel-line"
					cx=${R}
					cy=${R}
					r=${_e-14}
					stroke-width="0.6"
				/>
				${this.renderSpokes()} ${this.renderSigns()}
				${this.renderInterAspectLines(d,c,i)}
				${this.renderRing(d,zt,"p1",1)} ${this.renderRing(c,_e,"p2",2)}
				${this.renderAscendants(this.data)}
			</svg>
			<div class="legend-row">
				<span><span class="swatch" style="background: var(--roxy-accent)"></span>Person 1</span>
				<span><span class="swatch" style="background: var(--roxy-info)"></span>Person 2</span>
				<span><span class="swatch" style="background: var(--roxy-success)"></span>harmonious</span>
				<span><span class="swatch" style="background: var(--roxy-danger)"></span>challenging</span>
			</div>
			${g?s`<p class="summary">${g}</p>`:l}
			${i.length>0?this.renderAspects(i):l}
			${h.length>0||y.length>0?s`<div class="lists">
						${h.length?s`<div>
									<h3>Strengths</h3>
									<ul>
										${h.map(w=>s`<li>${w}</li>`)}
									</ul>
								</div>`:l}
						${y.length?s`<div>
									<h3>Challenges</h3>
									<ul>
										${y.map(w=>s`<li>${w}</li>`)}
									</ul>
								</div>`:l}
					</div>`:l}
		</div>`:s`<div
				class="wrap"
				aria-label="Synastry compatibility chart"
			>
				<div class="head">
					<h2 class="title">Synastry</h2>
					${typeof m=="number"?s`<span class="score" aria-label=${`Score ${m} of 100`}
								>${m} / 100</span
							>`:l}
				</div>
				<div class="missing-planets" role="status">
					Synastry response missing planet positions. Pass
					<code>data</code> with <code>person1.planets</code> and
					<code>person2.planets</code> arrays from the natal-chart endpoint, or
					use the <code>&lt;roxy-data&gt;</code> fallback.
				</div>
				${g?s`<p class="summary">${g}</p>`:l}
				${i.length>0?this.renderAspects(i):l}
				${h.length>0||y.length>0?s`<div class="lists">
							${h.length?s`<div>
										<h3>Strengths</h3>
										<ul>
											${h.map(w=>s`<li>${w}</li>`)}
										</ul>
									</div>`:l}
							${y.length?s`<div>
										<h3>Challenges</h3>
										<ul>
											${y.map(w=>s`<li>${w}</li>`)}
										</ul>
									</div>`:l}
						</div>`:l}
			</div>`}toAngle(e){return 180-e}renderSpokes(){return Array.from({length:12},(e,t)=>{let r=this.toAngle(t*30),o=P(R,R,_e-14,r),i=P(R,R,Nt,r);return k`<line class="wheel-line" x1=${o.x} y1=${o.y} x2=${i.x} y2=${i.y} stroke-width="0.6" />`})}renderSigns(){return _.map((e,t)=>{let r=this.toAngle(t*30+15),o=P(R,R,ha,r);return k`<text class="sign" x=${o.x} y=${o.y} text-anchor="middle" dominant-baseline="central">${G[e]}</text>`})}renderRing(e,t,r,o){return e.map(i=>{if(!Number.isFinite(i.longitude))return l;let d=this.toAngle(i.longitude),c=P(R,R,t,d),g=P(R,R,t+(o===1?-12:-10),d),h=M[E(i.name)]??i.name.slice(0,2),y=K(i.longitude),S=i.isRetrograde===!0,w=`${y.degree}\xB0${String(y.minute).padStart(2,"0")}'`,we=`${i.name}${S?" retrograde":""} - ${w} ${y.sign}`;return k`<g>
				<text class=${r} x=${c.x} y=${c.y} text-anchor="middle" dominant-baseline="central"><title>${we}</title>${h}<tspan class="person-tag" dy="-0.55em" dx="0.15em">${o}</tspan></text>
				<text class="planet-deg" x=${g.x} y=${g.y} text-anchor="middle" dominant-baseline="central">${y.degree}°${S?k`<tspan class="retro"> ℞</tspan>`:l}</text>
			</g>`})}renderAscendants(e){let t=[],r=(o,i)=>{if(!o)return;let d=_.findIndex(S=>S.toLowerCase()===o.sign.toLowerCase());if(d===-1)return;let c=d*30+o.degree,m=this.toAngle(c),g=i===1?zt+14:_e+14,h=P(R,R,g,m),y=P(R,R,Nt+14,m);t.push(k`<g>
				<line class="asc-tick" x1=${h.x} y1=${h.y} x2=${y.x} y2=${y.y} />
				<text class="asc-label" x=${y.x} y=${y.y} text-anchor="middle" dominant-baseline="central">Asc${i}</text>
			</g>`)};return r(e.person1?.ascendant,1),r(e.person2?.ascendant,2),t}renderInterAspectLines(e,t,r){let o=(i,d)=>{let c=E(d);for(let m of i)if(E(m.name)===c&&typeof m.longitude=="number")return m.longitude};return r.map(i=>{let d=o(e,i.planet1),c=o(t,i.planet2);if(d===void 0||c===void 0)return l;let m=P(R,R,zt-12,this.toAngle(d)),g=P(R,R,_e+8,this.toAngle(c)),h=Me(i),y=Ve[h]??"aspect-other",S=C(i.orb,1);return k`<line class=${`aspect ${y}`} x1=${m.x} y1=${m.y} x2=${g.x} y2=${g.y}><title>${i.planet1} ${h} ${i.planet2}${S?` (orb ${S}\xB0)`:""}</title></line>`})}renderAspects(e){return s`<table>
			<thead>
				<tr>
					<th>Planet 1</th>
					<th>Planet 2</th>
					<th>Aspect</th>
					<th>Orb</th>
					<th>Strength</th>
				</tr>
			</thead>
			<tbody>
				${e.slice(0,12).map(t=>s`<tr>
						<td>${t.planet1}</td>
						<td>${t.planet2}</td>
						<td>${Me(t)||""}</td>
						<td class="orb">${C(t.orb,1)}</td>
						<td>${ga(t.strength)}</td>
					</tr>`)}
			</tbody>
		</table>`}};fe.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.head {
				display: flex;
				justify-content: space-between;
				align-items: center;
				gap: var(--roxy-space-md, 1rem);
				flex-wrap: wrap;
			}

			.title {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
			}

			.score {
				font-variant-numeric: tabular-nums;
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-accent-fg, #b45309);
				font-size: var(--roxy-text-xl, 1.5rem);
			}

			svg {
				display: block;
				width: 100%;
				max-width: 560px;
				aspect-ratio: 1 / 1;
				height: auto;
				margin: 0 auto;
			}

			.wheel-line {
				fill: none;
				stroke: var(--roxy-border, #e4e4e7);
			}
			.sign {
				fill: var(--roxy-secondary, #475569);
				font-size: 14px;
			}
			.p1 {
				fill: var(--roxy-accent, #f59e0b);
				font-weight: 600;
				font-size: 13px;
			}
			.p2 {
				fill: var(--roxy-info, #0284c7);
				font-weight: 600;
				font-size: 13px;
			}
			.person-tag {
				font-size: 7px;
				font-weight: 700;
				opacity: 0.85;
			}
			.planet-deg {
				fill: var(--roxy-muted, #71717a);
				font-size: 7px;
				font-family: var(--roxy-font-sans);
			}
			.planet-deg .retro {
				fill: var(--roxy-danger, #dc2626);
			}
			.asc-tick {
				stroke: var(--roxy-accent-fg, #b45309);
				stroke-width: 1;
				opacity: 0.75;
			}
			.asc-label {
				fill: var(--roxy-accent-fg, #b45309);
				font-size: 9px;
				font-weight: 700;
				font-family: var(--roxy-font-sans);
				letter-spacing: 0.04em;
			}
			.aspect {
				stroke-width: 0.8;
				fill: none;
				opacity: 0.5;
			}
			.aspect-trine,
			.aspect-sextile {
				stroke: var(--roxy-success, #16a34a);
			}
			.aspect-square,
			.aspect-opposition {
				stroke: var(--roxy-danger, #dc2626);
			}
			.aspect-conjunction {
				stroke: var(--roxy-accent-fg, #b45309);
			}
			.aspect-other {
				stroke: var(--roxy-muted, #71717a);
				opacity: 0.35;
			}
			.legend-row {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				margin-top: calc(var(--roxy-space-xs, 0.25rem) * -1);
			}
			.legend-row .swatch {
				display: inline-block;
				width: 8px;
				height: 8px;
				border-radius: 50%;
				margin-right: 4px;
				vertical-align: middle;
			}

			.summary {
				margin: 0;
				color: var(--roxy-fg, #0a0a0a);
				font-size: var(--roxy-text-base, 1rem);
			}

			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				text-align: left;
			}
			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.06em;
			}
			td.orb {
				font-variant-numeric: tabular-nums;
				color: var(--roxy-muted, #71717a);
			}

			.lists {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
				gap: var(--roxy-space-md, 1rem);
			}
			.lists h3 {
				margin: 0 0 var(--roxy-space-xs, 0.25rem) 0;
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.lists ul {
				margin: 0;
				padding-left: var(--roxy-space-md, 1rem);
				font-size: var(--roxy-text-sm, 0.875rem);
			}

			.missing-planets {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 8%, transparent);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-md, 1rem);
				color: var(--roxy-fg, #0a0a0a);
				font-size: var(--roxy-text-sm, 0.875rem);
				line-height: 1.5;
			}
			.missing-planets code {
				font-family: var(--roxy-font-mono, ui-monospace, SFMono-Regular, Menlo, monospace);
				font-size: 0.95em;
				background: color-mix(in srgb, var(--roxy-fg, #0a0a0a) 6%, transparent);
				padding: 0 4px;
				border-radius: 4px;
			}
		`],p([u({attribute:!1})],fe.prototype,"data",2),fe=p([b("roxy-synastry-chart")],fe);function ga(n){return typeof n=="number"?Math.round(n).toString():""}var ne=class extends x{constructor(){super();this.data=null;this.flipped=!1;this.toggleFlip=()=>{this.flipped=!this.flipped};new $(this)}render(){let e=this.data;return e?"card"in e?this.renderDailyCard(e):this.renderFullCard(e):s`<div class="roxy-empty" role="status">No tarot data</div>`}renderDailyCard(e){let t=e.card,r=this.flipped!==!!t.reversed,o=t.keywords??[];return s`<article class="card" aria-label=${t.name??"Tarot card"}>
			<div class="image-wrap">
				${t.imageUrl?s`<img
							class=${`image ${r?"reversed":""}`}
							src=${t.imageUrl}
							alt=${t.name??"Tarot card"}
							tabindex="0"
							@click=${this.toggleFlip}
							@keydown=${i=>{(i.key==="Enter"||i.key===" ")&&(i.preventDefault(),this.toggleFlip())}}
						/>`:s`<div
							class=${`image ${r?"reversed":""}`}
							style="aspect-ratio: 0.6; display: flex; align-items: center; justify-content: center; color: var(--roxy-muted)"
						>
							${t.name??"?"}
						</div>`}
			</div>
			<div>
				<div class="meta">
					${t.arcana?s`${t.arcana} arcana`:l}
					${r?s` · reversed`:l}
				</div>
				<h2 class="title">${t.name??"Tarot card"}</h2>
				${e.dailyMessage?s`<p class="message">${e.dailyMessage}</p>`:l}
				${t.meaning?s`<p>${t.meaning}</p>`:l}
				${o.length>0?s`<div class="chips">
							${o.map(i=>s`<span>${i}</span>`)}
						</div>`:l}
				<button
					class="flip"
					type="button"
					@click=${this.toggleFlip}
					aria-pressed=${this.flipped?"true":"false"}
				>
					Flip card
				</button>
			</div>
		</article>`}renderFullCard(e){let t=this.flipped,r=t?e.reversed:e.upright,o=t?e.keywords?.reversed??[]:e.keywords?.upright??[];return s`<article class="card" aria-label=${e.name??"Tarot card"}>
			<div class="image-wrap">
				${e.imageUrl?s`<img
							class=${`image ${t?"reversed":""}`}
							src=${e.imageUrl}
							alt=${e.name??"Tarot card"}
							tabindex="0"
							@click=${this.toggleFlip}
							@keydown=${i=>{(i.key==="Enter"||i.key===" ")&&(i.preventDefault(),this.toggleFlip())}}
						/>`:s`<div
							class=${`image ${t?"reversed":""}`}
							style="aspect-ratio: 0.6; display: flex; align-items: center; justify-content: center; color: var(--roxy-muted)"
						>
							${e.name??"?"}
						</div>`}
			</div>
			<div>
				<div class="meta">
					${e.arcana?s`${e.arcana} arcana`:l}
					${e.number!==void 0&&e.number!==null?s` · ${e.number}`:l}
					${t?s` · reversed`:l}
				</div>
				<h2 class="title">${e.name??"Tarot card"}</h2>
				${r?.description?s`<p>${r.description}</p>`:l}
				${o.length>0?s`<div class="chips">
							${o.map(i=>s`<span>${i}</span>`)}
						</div>`:l}
				<button
					class="flip"
					type="button"
					@click=${this.toggleFlip}
					aria-pressed=${this.flipped?"true":"false"}
				>
					Flip card
				</button>
			</div>
		</article>`}};ne.styles=[v,f`
			.card {
				background: var(--roxy-bg, #fff);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-lg, 1.5rem);
				box-shadow: var(--roxy-shadow-sm);
				display: grid;
				grid-template-columns: minmax(0, 9rem) 1fr;
				gap: var(--roxy-space-lg, 1.5rem);
				align-items: start;
			}

			@container (max-width: 480px) {
				.card {
					grid-template-columns: 1fr;
				}
			}

			.image-wrap {
				perspective: 800px;
			}
			.image {
				display: block;
				width: 100%;
				height: auto;
				border-radius: var(--roxy-radius-md, 8px);
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 60%, transparent);
				transition:
					transform var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
				cursor: pointer;
			}
			.image.reversed {
				transform: rotate(180deg);
			}
			.image:focus-visible {
				outline: 2px solid var(--roxy-ring, rgba(245, 158, 11, 0.4));
				outline-offset: 2px;
			}

			.title {
				margin: 0;
				font-size: var(--roxy-text-xl, 1.5rem);
				font-weight: var(--roxy-weight-bold, 600);
				letter-spacing: var(--roxy-tracking-tight);
			}
			.meta {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
				text-transform: uppercase;
				letter-spacing: 0.06em;
				margin-bottom: var(--roxy-space-sm, 0.5rem);
			}

			.message {
				color: var(--roxy-fg, #0a0a0a);
				margin: var(--roxy-space-sm, 0.5rem) 0 var(--roxy-space-md, 1rem);
			}

			.chips {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-xs, 0.25rem);
				margin-top: var(--roxy-space-sm, 0.5rem);
			}
			.chips span {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 14%, transparent);
				padding: 2px 8px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
			}

			.flip {
				margin-top: var(--roxy-space-sm, 0.5rem);
				background: transparent;
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: 4px 12px;
				font-family: inherit;
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-secondary, #475569);
				cursor: pointer;
				transition:
					transform var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}
			.flip:hover {
				transform: scale(1.02);
			}
		`],p([u({attribute:!1})],ne.prototype,"data",2),p([z()],ne.prototype,"flipped",2),ne=p([b("roxy-tarot-card")],ne);var ie=class extends x{constructor(){super();this.data=null;this.spread="three-card";new $(this)}render(){let e=this.data;if(!e)return s`<div class="roxy-empty" role="status">No tarot spread</div>`;let t="answer"in e,r="cards"in e&&!("spread"in e),o=r?[]:"positions"in e?e.positions??[]:[],i=r&&"cards"in e?e.cards:[],d=t?e.answer:void 0,c=t?e.strength:void 0,m="spread"in e?e.spread:this.spread.replace(/-/g," "),g="question"in e?e.question:void 0,h="summary"in e?e.summary:void 0,y=t?e.interpretation:void 0,S=d?d.toLowerCase().replace(/[^a-z]/g,""):"";return s`<article class="wrap" aria-label="Tarot spread">
			<header class="head">
				<h2 class="title">${m}</h2>
				${g?s`<span class="question">"${g}"</span>`:l}
			</header>
			${t?s`<div>
						<span class=${`answer ${S}`}>${d}</span>
						${c?s`<small> · ${c}</small>`:l}
					</div>`:l}
			${o.length>0?s`<div class="grid">
						${o.map(w=>s`<div class="card">
								<p class="label">${w.name??""}</p>
								<div class="image">
									${w.card?.imageUrl?s`<img
												src=${w.card.imageUrl}
												alt=${w.card.name??"tarot card"}
												class=${w.card.reversed?"reversed":""}
											/>`:s`${w.card?.name??"?"}`}
								</div>
								<p class="name">
									${w.card?.name??""}
									${w.card?.reversed?s`<small>(reversed)</small>`:l}
								</p>
								${w.interpretation?s`<p class="interp">${w.interpretation}</p>`:l}
							</div>`)}
					</div>`:l}
			${i.length>0?s`<div class="grid">
						${i.map(w=>s`<div class="card">
								<div class="image">
									${w.imageUrl?s`<img
												src=${w.imageUrl}
												alt=${w.name??"tarot card"}
												class=${w.reversed?"reversed":""}
											/>`:s`${w.name??"?"}`}
								</div>
								<p class="name">
									${w.name??""}
									${w.reversed?s`<small>(reversed)</small>`:l}
								</p>
								${w.meaning?s`<p class="interp">${w.meaning}</p>`:l}
							</div>`)}
					</div>`:l}
			${h?s`<p class="reading">${h}</p>`:l}
			${y?s`<p class="reading">${y}</p>`:l}
		</article>`}};ie.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.head {
				display: flex;
				justify-content: space-between;
				gap: var(--roxy-space-md, 1rem);
				flex-wrap: wrap;
				align-items: baseline;
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: capitalize;
			}
			.question {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
				font-style: italic;
			}

			.answer {
				display: inline-block;
				padding: 4px 14px;
				border-radius: var(--roxy-radius-full, 9999px);
				font-weight: var(--roxy-weight-bold, 600);
				font-size: var(--roxy-text-base, 1rem);
				text-transform: uppercase;
				letter-spacing: 0.06em;
			}
			.answer.yes {
				background: color-mix(in srgb, var(--roxy-success, #16a34a) 16%, transparent);
				color: var(--roxy-success-fg, #166534);
			}
			.answer.no {
				background: color-mix(in srgb, var(--roxy-danger, #dc2626) 16%, transparent);
				color: var(--roxy-danger-fg, #991b1b);
			}
			.answer.maybe {
				background: color-mix(in srgb, var(--roxy-warning, #ea580c) 16%, transparent);
				color: var(--roxy-warning-fg, #9a3412);
			}

			.grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr));
				gap: var(--roxy-space-md, 1rem);
			}

			.card {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-sm, 0.5rem);
				background: var(--roxy-bg, #fff);
				display: grid;
				gap: var(--roxy-space-xs, 0.25rem);
			}
			.label {
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.06em;
				margin: 0;
			}
			.image {
				width: 100%;
				aspect-ratio: 0.6;
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 60%, transparent);
				border-radius: var(--roxy-radius-sm, 4px);
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				overflow: hidden;
			}
			.image img {
				width: 100%;
				height: 100%;
				object-fit: cover;
				transition:
					transform var(--roxy-motion-duration, 200ms)
					var(--roxy-motion-easing, cubic-bezier(0.4, 0, 0.2, 1));
			}
			.image img.reversed {
				transform: rotate(180deg);
			}
			.name {
				margin: 0;
				font-size: var(--roxy-text-sm, 0.875rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.interp {
				margin: 0;
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-secondary, #475569);
			}

			.reading {
				margin: 0;
				color: var(--roxy-fg, #0a0a0a);
			}
		`],p([u({attribute:!1})],ie.prototype,"data",2),p([u({type:String,reflect:!0})],ie.prototype,"spread",2),ie=p([b("roxy-tarot-spread")],ie);var be=class extends x{constructor(){super();this.data=null;new $(this)}render(){if(!this.data?.transitPlanets?.length)return s`<div class="roxy-empty" role="status">No transits data</div>`;let{transitDate:e,transitTime:t,transitPlanets:r,transitAspects:o,summary:i}=this.data,d=[at(e),I(t)].filter(Boolean).join(" ");return s`<div class="wrap" aria-label="Transit positions table">
			<div class="head">
				<h2 class="title">Transits</h2>
				${d?s`<p class="subtitle">${d}</p>`:l}
			</div>

			${i?this.renderSummaryPills(i):l}

			<div>
				<p class="section-label">Planet positions</p>
				<div class="overflow-scroll">
					${this.renderPlanetsTable(r)}
				</div>
			</div>

			${o?.length?s`<div>
						<p class="section-label">Transit aspects</p>
						<div class="overflow-scroll">
							${this.renderAspectsList(o)}
						</div>
					</div>`:l}
		</div>`}renderSummaryPills(e){return s`<div class="summary-pills" role="region" aria-label="Aspect summary">
			<span class="pill pill--muted">
				Total: ${e.totalAspects}
			</span>
			<span class="pill pill--success">
				Harmonious: ${e.harmonious}
			</span>
			<span class="pill pill--danger">
				Challenging: ${e.challenging}
			</span>
			<span class="pill pill--muted">
				Neutral: ${e.neutral}
			</span>
		</div>`}renderPlanetsTable(e){return s`<table class="planets-table">
			<thead>
				<tr>
					<th scope="col">Planet</th>
					<th scope="col">Sign</th>
					<th scope="col">Degree</th>
					<th scope="col">Speed</th>
				</tr>
			</thead>
			<tbody>
				${e.map(t=>{let r=M[E(t.name)]??"",o=G[E(t.sign)]??"",i=t.speed>=0?"\u2191":"\u2193";return s`<tr>
						<td>
							<div class="planet-cell">
								<span class="glyph" aria-hidden="true">${r}</span>
								${t.name}
								${t.isRetrograde?s`<span class="retro-badge" aria-label="retrograde">R</span>`:l}
							</div>
						</td>
						<td>
							<div class="planet-cell">
								<span class="glyph" aria-hidden="true">${o}</span>
								${t.sign}
							</div>
						</td>
						<td class="num">${C(t.degree,2)}</td>
						<td class="speed">
							<span class="speed-arrow" aria-hidden="true">${i}</span>
							${C(Math.abs(t.speed),4)}
						</td>
					</tr>`})}
			</tbody>
		</table>`}renderAspectsList(e){return s`<div role="list" aria-label="Transit aspects">
			${e.map((t,r)=>{let o=M[E(t.transitPlanet)]??"",i=M[E(t.natalPlanet)]??"",d=(t.nature??"neutral").toLowerCase(),c=t.interpretation,m=(t.type??"").toLowerCase(),g=t.isApplying?"Applying":"Separating";return s`<details class="aspect-card" role="listitem" name="transit-aspects" ?open=${r===0}>
					<summary>
						<span aria-hidden="true">${o}</span>
						${t.transitPlanet}
						<span class="nature-badge ${d}">${m}</span>
						<span aria-hidden="true">${i}</span>
						${t.natalPlanet}
						<span class="meta">
							${g} · orb ${C(t.orb,2)}° · strength ${C(t.strength,1)}
						</span>
					</summary>
					<div class="interp-body">
						${c?.summary?s`<p>${c.summary}</p>`:l}
						${c?.impact?s`<p><strong>Impact:</strong> ${c.impact}</p>`:l}
						${c?.timing?s`<p><strong>Timing:</strong> ${c.timing}</p>`:l}
						${c?.guidance?s`<p><strong>Guidance:</strong> ${c.guidance}</p>`:l}
						${c?.keywords?.length?s`<div class="interp-keywords">
										${c.keywords.map(h=>s`<span class="kw">${h}</span>`)}
									</div>`:l}
					</div>
				</details>`})}
		</div>`}};be.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}

			.head {
				display: flex;
				justify-content: space-between;
				align-items: baseline;
				gap: var(--roxy-space-md, 1rem);
				flex-wrap: wrap;
			}

			.title {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
			}

			.subtitle {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
				margin: 0;
			}

			.summary-pills {
				display: flex;
				flex-wrap: wrap;
				gap: var(--roxy-space-sm, 0.5rem);
			}

			.pill {
				display: inline-flex;
				align-items: center;
				gap: 4px;
				padding: 2px var(--roxy-space-sm, 0.5rem);
				border-radius: var(--roxy-radius-full, 9999px);
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: var(--roxy-weight-bold, 600);
				border: 1px solid currentColor;
			}

			.pill--muted {
				color: var(--roxy-fg, #0a0a0a);
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 60%, transparent);
			}

			.pill--success {
				color: var(--roxy-success-fg, #166534);
				background: color-mix(in srgb, var(--roxy-success, #16a34a) 10%, transparent);
			}

			.pill--danger {
				color: var(--roxy-danger-fg, #991b1b);
				background: color-mix(in srgb, var(--roxy-danger, #dc2626) 10%, transparent);
			}

			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
			}

			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
				text-align: left;
			}

			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.06em;
			}

			.section-label {
				font-size: var(--roxy-text-xs, 0.75rem);
				color: var(--roxy-muted, #71717a);
				text-transform: uppercase;
				letter-spacing: 0.06em;
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0 0 var(--roxy-space-xs, 0.25rem) 0;
			}

			.glyph {
				font-size: 1.1em;
				margin-right: 2px;
				line-height: 1;
			}

			.planet-cell {
				display: flex;
				align-items: center;
				gap: 4px;
				white-space: nowrap;
			}

			.retro-badge {
				display: inline-block;
				font-size: 0.7em;
				padding: 1px 4px;
				border-radius: var(--roxy-radius-sm, 4px);
				background: color-mix(in srgb, var(--roxy-warning, #ea580c) 12%, transparent);
				color: var(--roxy-warning-fg, #9a3412);
				font-weight: var(--roxy-weight-bold, 600);
				margin-left: 2px;
				vertical-align: middle;
			}

			.speed {
				font-variant-numeric: tabular-nums;
				color: var(--roxy-muted, #71717a);
				white-space: nowrap;
			}

			.speed-arrow {
				font-size: 0.85em;
			}

			td.num {
				font-variant-numeric: tabular-nums;
				color: var(--roxy-muted, #71717a);
			}

			.overflow-scroll {
				overflow-x: auto;
				-webkit-overflow-scrolling: touch;
			}

			.aspect-card {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				margin-bottom: var(--roxy-space-xs, 0.25rem);
			}
			.aspect-card summary {
				cursor: pointer;
				font-weight: 500;
				color: var(--roxy-fg, #0a0a0a);
				display: flex;
				flex-wrap: wrap;
				align-items: center;
				gap: 0.5em;
			}
			.aspect-card summary .meta {
				color: var(--roxy-muted, #71717a);
				font-weight: 400;
				font-size: var(--roxy-text-xs, 0.75rem);
				margin-left: auto;
				font-variant-numeric: tabular-nums;
			}
			.aspect-card .interp-body {
				margin-top: var(--roxy-space-xs, 0.25rem);
				color: var(--roxy-fg, #0a0a0a);
				font-size: var(--roxy-text-sm, 0.875rem);
				line-height: 1.45;
			}
			.aspect-card .interp-body p {
				margin: 0 0 var(--roxy-space-xs, 0.25rem);
			}
			.interp-keywords {
				display: flex;
				flex-wrap: wrap;
				gap: 0.25rem;
				margin-top: 0.5rem;
			}
			.interp-keywords .kw {
				padding: 1px 8px;
				border-radius: 9999px;
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 14%, transparent);
				color: var(--roxy-accent-fg, #b45309);
				font-size: var(--roxy-text-xs, 0.75rem);
			}
			.nature-badge {
				display: inline-block;
				padding: 1px 8px;
				border-radius: 9999px;
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: 600;
			}
			.nature-badge.harmonious {
				background: color-mix(in srgb, var(--roxy-success, #16a34a) 12%, transparent);
				color: var(--roxy-success-fg, #166534);
			}
			.nature-badge.challenging {
				background: color-mix(in srgb, var(--roxy-danger, #dc2626) 12%, transparent);
				color: var(--roxy-danger-fg, #991b1b);
			}
			.nature-badge.neutral {
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 60%, transparent);
				color: var(--roxy-fg, #0a0a0a);
			}
		`],p([u({attribute:!1})],be.prototype,"data",2),be=p([b("roxy-transits-table")],be);var le=class extends x{constructor(){super();this.data=null;this.chartStyle="north";this.setStyle=e=>{this.chartStyle=e};new $(this)}viewModel(){return this.data?.meta?nt(this.data.meta,"D1 Rashi"):null}render(){let e=this.viewModel();return e?s`<div class="wrap">
			<div class="header">
				<h2 class="title">Vedic kundli</h2>
				${lt(this.chartStyle,this.setStyle)}
			</div>
			<svg
				viewBox="0 0 400 400"
				preserveAspectRatio="xMidYMid meet"
				role="img"
				aria-label="Vedic birth chart with twelve sign houses"
			>
				<title>Vedic kundli</title>
				${it(e,this.chartStyle)}
			</svg>
		</div>`:s`<div class="roxy-empty" role="status">No kundli data</div>`}};le.styles=[v,dt],p([u({attribute:!1})],le.prototype,"data",2),p([u({type:String,reflect:!0,attribute:"chart-style"})],le.prototype,"chartStyle",2),le=p([b("roxy-vedic-kundli")],le);var ua=["Lagna","Sun","Moon","Mars","Mercury","Jupiter","Venus","Saturn","Rahu","Ketu"],ve=class extends x{constructor(){super();this.data=null;new $(this)}orderedRows(){let e=this.data?.meta??{},t=new Set,r=[];for(let o of ua){let i=e[o];i&&(r.push([o,i]),t.add(o))}for(let[o,i]of Object.entries(e))t.has(o)||r.push([o,i]);return r}render(){if(!this.data?.meta)return s`<div class="roxy-empty" role="status">No chart data</div>`;let e=this.orderedRows();return s`<div class="wrap" aria-label="Vedic planetary positions" tabindex="0">
			<header class="head">
				<h2 class="title">Planetary positions</h2>
			</header>
			<table role="table">
				<thead>
					<tr>
						<th scope="col">Graha</th>
						<th scope="col">Rashi</th>
						<th scope="col">Degree</th>
						<th scope="col">Nakshatra</th>
						<th scope="col">Pada</th>
						<th scope="col">Nak. lord</th>
						<th scope="col">House</th>
						<th scope="col">Avastha</th>
						<th scope="col">Retro</th>
					</tr>
				</thead>
				<tbody>
					${e.map(([t,r])=>{let o=(r.graha??t)==="Lagna",i=M[E(r.graha??t)]??"",d=G[E(r.rashi??"")]??"";return s`<tr class=${o?"lagna":""}>
							<td class="graha">
								${i?s`<span class="glyph">${i}</span>`:l}${r.graha??t}
							</td>
							<td>
								${d?s`<span class="glyph">${d}</span>`:l}${r.rashi??""}
							</td>
							<td class="num">
								${typeof r.longitude=="number"?st(r.longitude):""}
							</td>
							<td>${r.nakshatra?.name??""}</td>
							<td class="num">${r.nakshatra?.pada??""}</td>
							<td>${r.nakshatra?.lord??""}</td>
							<td class="num">${typeof r.house=="number"?r.house:""}</td>
							<td>${r.awastha??""}</td>
							<td>${r.isRetrograde?s`<span class="retro">R</span>`:l}</td>
						</tr>`})}
				</tbody>
			</table>
		</div>`}};ve.styles=[v,f`
			.wrap {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				background: var(--roxy-bg, #fff);
				overflow: auto;
				box-shadow: var(--roxy-shadow-sm);
			}
			.head {
				padding: var(--roxy-space-md, 1rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
				min-width: 620px;
			}
			thead {
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 20%, transparent);
			}
			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				text-align: left;
				white-space: nowrap;
			}
			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.04em;
			}
			tbody tr {
				border-top: 1px solid var(--roxy-border, #e4e4e7);
			}
			tbody tr.lagna {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 10%, transparent);
			}
			td.graha {
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-fg, #0a0a0a);
			}
			.glyph {
				margin-right: 0.4em;
				color: var(--roxy-muted, #71717a);
			}
			/* On the tinted Lagna row the muted glyph drops below the WCAG AA
			   contrast floor, so use the accent foreground there instead. */
			tbody tr.lagna .glyph {
				color: var(--roxy-accent-fg, #b45309);
			}
			.retro {
				color: var(--roxy-warning-fg, #9a3412);
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.num {
				font-variant-numeric: tabular-nums;
			}
		`],p([u({attribute:!1})],ve.prototype,"data",2),ve=p([b("roxy-vedic-planets-table")],ve);var $e=class extends x{constructor(){super();this.data=null;new $(this)}rows(){let e=this.data;if(!e)return[];let t=(e.planets??[]).map(r=>({name:r.name,sign:r.sign,longitude:r.longitude,house:r.house,speed:r.speed,isRetrograde:r.isRetrograde}));for(let[r,o]of[["Ascendant",e.ascendant],["Midheaven",e.midheaven],["Part of Fortune",e.partOfFortune],["Vertex",e.vertex]])o&&t.push({name:r,sign:o.sign,longitude:o.longitude,isPoint:!0});return t}render(){if(!this.data?.planets)return s`<div class="roxy-empty" role="status">No chart data</div>`;let e=this.rows();return s`<div class="wrap" aria-label="Western planetary positions" tabindex="0">
			<header class="head">
				<h2 class="title">Planetary positions</h2>
			</header>
			<table role="table">
				<thead>
					<tr>
						<th scope="col">Body</th>
						<th scope="col">Sign</th>
						<th scope="col">Degree</th>
						<th scope="col">House</th>
						<th scope="col">Motion</th>
					</tr>
				</thead>
				<tbody>
					${e.map(t=>{let r=M[E(t.name)]??"",o=G[E(t.sign??"")]??"",i=typeof t.speed=="number"?C(t.speed,3):"";return s`<tr class=${t.isPoint?"point":""}>
							<td class="body">
								${r?s`<span class="glyph">${r}</span>`:l}${t.name}
							</td>
							<td>
								${o?s`<span class="glyph">${o}</span>`:l}${t.sign??""}
							</td>
							<td class="num">
								${typeof t.longitude=="number"?st(t.longitude):""}
							</td>
							<td class="num">${typeof t.house=="number"?t.house:""}</td>
							<td class="num">
								${i?s`${i}°/day`:l}
								${t.isRetrograde?s`<span class="retro"> ℞</span>`:l}
							</td>
						</tr>`})}
				</tbody>
			</table>
		</div>`}};$e.styles=[v,f`
			.wrap {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				background: var(--roxy-bg, #fff);
				overflow: auto;
				box-shadow: var(--roxy-shadow-sm);
			}
			.head {
				padding: var(--roxy-space-md, 1rem);
				border-bottom: 1px solid var(--roxy-border, #e4e4e7);
			}
			.title {
				margin: 0;
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
			}
			table {
				width: 100%;
				border-collapse: collapse;
				font-size: var(--roxy-text-sm, 0.875rem);
				min-width: 460px;
			}
			thead {
				background: color-mix(in srgb, var(--roxy-border, #e4e4e7) 20%, transparent);
			}
			th,
			td {
				padding: var(--roxy-space-sm, 0.5rem) var(--roxy-space-md, 1rem);
				text-align: left;
				white-space: nowrap;
			}
			th {
				color: var(--roxy-muted, #71717a);
				font-weight: var(--roxy-weight-bold, 600);
				text-transform: uppercase;
				font-size: var(--roxy-text-xs, 0.75rem);
				letter-spacing: 0.04em;
			}
			tbody tr {
				border-top: 1px solid var(--roxy-border, #e4e4e7);
			}
			tbody tr.point {
				background: color-mix(in srgb, var(--roxy-accent, #f59e0b) 8%, transparent);
			}
			td.body {
				font-weight: var(--roxy-weight-bold, 600);
				color: var(--roxy-fg, #0a0a0a);
			}
			.glyph {
				margin-right: 0.4em;
				color: var(--roxy-muted, #71717a);
			}
			.retro {
				color: var(--roxy-danger, #dc2626);
				font-weight: var(--roxy-weight-bold, 600);
			}
			.num {
				font-variant-numeric: tabular-nums;
			}
		`],p([u({attribute:!1})],$e.prototype,"data",2),$e=p([b("roxy-western-planets-table")],$e);var de=class extends x{constructor(){super();this.data=null;this.filter="";this.handleInput=e=>{this.filter=e.target.value};new $(this)}renderQualityChip(e){let t=`quality-chip quality-${e}`;return s`<span class=${t}>${e}</span>`}renderDetailCard(e){return s`<div class="detail-card">
			<p class="detail-name">
				${e.name}
				${e.quality?this.renderQualityChip(e.quality):l}
			</p>
			${e.description?s`<p class="description">${e.description}</p>`:l}
			${e.result?s`<details>
						<summary>Effects</summary>
						<div class="result-body">${e.result}</div>
					</details>`:l}
		</div>`}render(){if(!this.data)return s`<div class="roxy-empty" role="status">No yoga data</div>`;let e=this.data,t=this.filter.toLowerCase();if("description"in e&&typeof e.description=="string"){let r=e;return s`<div class="wrap">${this.renderDetailCard(r)}</div>`}if("yogas"in e&&Array.isArray(e.yogas)){let r=e.yogas;if(r.length>0&&"description"in r[0]){let m=r,g=t?m.filter(y=>y.name.toLowerCase().includes(t)):m,h=e.total;return s`<div class="wrap">
					<div class="head">
						<h2 class="title">Yoga catalog</h2>
						${h!==void 0?s`<span class="count">${h} total</span>`:l}
					</div>
					<div class="search-wrap">
						<input
							class="search"
							type="search"
							placeholder="Filter yogas..."
							aria-label="Filter yoga list by name"
							.value=${this.filter}
							@input=${this.handleInput}
						/>
					</div>
					<div
						class="detail-grid"
						role="region"
						aria-live="polite"
						aria-label="Yoga results"
					>
						${g.length>0?g.map(y=>this.renderDetailCard(y)):s`<p class="no-results">No yogas match your search.</p>`}
					</div>
				</div>`}let i=r,d=t?i.filter(m=>m.name.toLowerCase().includes(t)):i,c=e.total;return s`<div class="wrap">
				<div class="head">
					<h2 class="title">Yoga catalog</h2>
					${c!==void 0?s`<span class="count">${c} total</span>`:l}
				</div>
				<div class="search-wrap">
					<input
						class="search"
						type="search"
						placeholder="Filter yogas..."
						aria-label="Filter yoga list by name"
						.value=${this.filter}
						@input=${this.handleInput}
					/>
				</div>
				<div
					class="grid"
					role="region"
					aria-live="polite"
					aria-label="Yoga results"
				>
					${d.length>0?d.map(m=>s`<div class="yoga-chip">
									${m.name}
									<span class="yoga-id">${m.id}</span>
								</div>`):s`<p class="no-results">No yogas match your search.</p>`}
				</div>
			</div>`}return s`<div class="roxy-empty" role="status">No yoga data</div>`}};de.styles=[v,f`
			.wrap {
				display: grid;
				gap: var(--roxy-space-md, 1rem);
			}
			.head {
				display: flex;
				justify-content: space-between;
				align-items: baseline;
				flex-wrap: wrap;
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.title {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
			}
			.count {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
			}
			.search-wrap {
				display: flex;
				align-items: center;
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.search {
				width: 100%;
				max-width: 280px;
				padding: 0.35em 0.75em;
				font-size: var(--roxy-text-sm, 0.875rem);
				font-family: var(--roxy-font-sans);
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				background: var(--roxy-bg, #fff);
				color: var(--roxy-fg, #0a0a0a);
				outline: none;
			}
			.search::placeholder {
				color: var(--roxy-fg, #0a0a0a);
				opacity: 0.65;
			}
			.search:focus {
				border-color: var(--roxy-accent, #f59e0b);
				box-shadow: 0 0 0 2px color-mix(in srgb, var(--roxy-accent, #f59e0b) 30%, transparent);
			}
			.grid {
				display: grid;
				gap: var(--roxy-space-sm, 0.5rem);
				grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
			}
			.yoga-chip {
				padding: 0.4em 0.8em;
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				font-size: var(--roxy-text-sm, 0.875rem);
				background: var(--roxy-bg, #fff);
				color: var(--roxy-fg, #0a0a0a);
				word-break: break-word;
			}
			.yoga-chip .yoga-id {
				display: block;
				font-size: 0.7em;
				color: var(--roxy-fg, #0a0a0a);
				opacity: 0.75;
				margin-top: 0.15em;
			}
			.detail-card {
				border: 1px solid var(--roxy-border, #e4e4e7);
				border-radius: var(--roxy-radius-md, 8px);
				padding: var(--roxy-space-md, 1rem);
				background: var(--roxy-bg, #fff);
				display: grid;
				gap: var(--roxy-space-sm, 0.5rem);
			}
			.detail-name {
				font-size: var(--roxy-text-lg, 1.125rem);
				font-weight: var(--roxy-weight-bold, 600);
				margin: 0;
				display: flex;
				align-items: center;
				gap: var(--roxy-space-sm, 0.5rem);
				flex-wrap: wrap;
			}
			.quality-chip {
				display: inline-block;
				font-size: var(--roxy-text-xs, 0.75rem);
				font-weight: 600;
				padding: 0.15em 0.6em;
				border-radius: 999px;
			}
			.quality-Positive {
				background: color-mix(in srgb, var(--roxy-success, #22c55e) 18%, transparent);
				color: var(--roxy-success-fg, #15803d);
				border: 1px solid color-mix(in srgb, var(--roxy-success, #22c55e) 40%, transparent);
			}
			.quality-Negative {
				background: color-mix(in srgb, var(--roxy-danger, #ef4444) 18%, transparent);
				color: var(--roxy-danger-fg, #b91c1c);
				border: 1px solid color-mix(in srgb, var(--roxy-danger, #ef4444) 40%, transparent);
			}
			.quality-Both {
				background: color-mix(in srgb, var(--roxy-warning, #f59e0b) 18%, transparent);
				color: var(--roxy-warning-fg, #b45309);
				border: 1px solid color-mix(in srgb, var(--roxy-warning, #f59e0b) 40%, transparent);
			}
			.description {
				font-size: var(--roxy-text-sm, 0.875rem);
				color: var(--roxy-muted, #71717a);
				margin: 0;
				line-height: var(--roxy-leading-normal, 1.5);
			}
			details {
				font-size: var(--roxy-text-sm, 0.875rem);
			}
			details summary {
				cursor: pointer;
				color: var(--roxy-accent-fg, #b45309);
				font-weight: 500;
				padding: 0.25em 0;
				list-style: none;
				display: flex;
				align-items: center;
				gap: 0.4em;
			}
			details summary::before {
				content: '+';
				font-size: 1.1em;
				line-height: 1;
			}
			details[open] summary::before {
				content: '-';
			}
			details .result-body {
				padding-top: var(--roxy-space-xs, 0.25rem);
				color: var(--roxy-fg, #0a0a0a);
				line-height: var(--roxy-leading-normal, 1.5);
			}
			.no-results {
				color: var(--roxy-muted, #71717a);
				font-size: var(--roxy-text-sm, 0.875rem);
				padding: var(--roxy-space-md, 1rem) 0;
				text-align: center;
			}
			.detail-grid {
				display: grid;
				gap: var(--roxy-space-sm, 0.5rem);
			}
		`],p([u({attribute:!1})],de.prototype,"data",2),p([z()],de.prototype,"filter",2),de=p([b("roxy-yoga-list")],de);var ct=[{pascal:"RoxyNatalChart",tag:"roxy-natal-chart",slug:"natal-chart",heading:"Natal chart",description:"Western natal chart wheel for /astrology/natal-chart responses",docsLabel:"Western",endpointLabel:"POST /astrology/natal-chart",docsSummary:"Natal chart wheel with planet glyphs and aspect lines",topic:"Astrology"},{pascal:"RoxySynastryChart",tag:"roxy-synastry-chart",slug:"synastry-chart",heading:"Synastry",description:"Dual-wheel synastry chart with inter-aspects table",docsLabel:"Western",endpointLabel:"POST /astrology/synastry",docsSummary:"Dual-wheel synastry with inter-aspects table",topic:"Astrology"},{pascal:"RoxyWesternPlanetsTable",tag:"roxy-western-planets-table",slug:"western-planets-table",heading:"Western planets",description:"Western planetary positions table with sign, degree, house, and motion plus the chart angles",docsLabel:"Western",endpointLabel:"POST /astrology/natal-chart",docsSummary:"Sign, degree, house, motion columns plus ASC, MC, PoF, Vertex",topic:"Astrology"},{pascal:"RoxyTransitsTable",tag:"roxy-transits-table",slug:"transits-table",heading:"Transits",description:"Live planet positions plus aspects to a natal chart",docsLabel:"Western",endpointLabel:"POST /astrology/transits",docsSummary:"Transit planet positions plus optional aspects to a natal chart",topic:"Astrology"},{pascal:"RoxyMoonPhase",tag:"roxy-moon-phase",slug:"moon-phase",heading:"Moon phase",description:"Moon phase card and calendar",docsLabel:"Western",endpointLabel:"GET /astrology/moon-phase/{current,upcoming,calendar/...}",docsSummary:"Moon phase card and calendar",topic:"Astrology"},{pascal:"RoxyHoroscopeCard",tag:"roxy-horoscope-card",slug:"horoscope-card",heading:"Daily horoscope",description:"Daily, weekly, or monthly horoscope card for /astrology/horoscope/...",docsLabel:"Western",endpointLabel:"GET /astrology/horoscope/{sign}/{daily,weekly,monthly}",docsSummary:"Daily, weekly, or monthly horoscope card",topic:"Astrology"},{pascal:"RoxyCompatibilityCard",tag:"roxy-compatibility-card",slug:"compatibility-card",heading:"Compatibility score",description:"Cross-domain compatibility score card",docsLabel:"Cross",endpointLabel:"POST /astrology/compatibility-score, /numerology/compatibility, /biorhythm/compatibility",docsSummary:"Score card with category breakdown",topic:"Astrology"},{pascal:"RoxyVedicKundli",tag:"roxy-vedic-kundli",slug:"vedic-kundli",heading:"Vedic kundli",description:"South, North, or East Indian Vedic kundli for /vedic-astrology/birth-chart with per-planet degree and nakshatra detail",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/birth-chart",docsSummary:"South, North, or East Indian kundli with degree detail",topic:"Vedic"},{pascal:"RoxyDivisionalChart",tag:"roxy-divisional-chart",slug:"divisional-chart",heading:"Divisional chart",description:"D2 to D60 varga chart wheel with Vargottama markers",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/divisional-chart",docsSummary:"Generic divisional varga wheel from D2 Hora to D60 Shashtiamsa",topic:"Vedic"},{pascal:"RoxyKpChart",tag:"roxy-kp-chart",slug:"kp-chart",heading:"KP chart",description:"Full KP chart with Ascendant, Placidus cusps, and planets in tabbed stellar-hierarchy tables",docsLabel:"Vedic (KP)",endpointLabel:"POST /vedic-astrology/kp/chart",docsSummary:"Ascendant, cusps, and planets with KP stellar hierarchy",topic:"Vedic"},{pascal:"RoxyVedicPlanetsTable",tag:"roxy-vedic-planets-table",slug:"vedic-planets-table",heading:"Vedic planets",description:"Vedic planetary positions table with degree, nakshatra, pada, nakshatra lord, bhava, and avastha",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/birth-chart",docsSummary:"Degree, nakshatra, pada, lord, bhava, avastha columns",topic:"Vedic"},{pascal:"RoxyKpPlanetsTable",tag:"roxy-kp-planets-table",slug:"kp-planets-table",heading:"KP planets",description:"KP planets table with sub-lord and sub-sub-lord columns",docsLabel:"Vedic (KP)",endpointLabel:"POST /vedic-astrology/kp/planets",docsSummary:"Sub-lord and sub-sub-lord columns",topic:"Vedic"},{pascal:"RoxyKpRulingPlanets",tag:"roxy-kp-ruling-planets",slug:"kp-ruling-planets",heading:"KP ruling planets",description:"KP ruling planets with day lord, Moon and Lagna stellar hierarchies, and house significators",docsLabel:"Vedic (KP)",endpointLabel:"POST /vedic-astrology/kp/ruling-planets",docsSummary:"Day lord, Moon/Lagna hierarchies, ruling planets, significators",topic:"Vedic"},{pascal:"RoxyAshtakavargaGrid",tag:"roxy-ashtakavarga-grid",slug:"ashtakavarga-grid",heading:"Ashtakavarga",description:"Sarva and Bhinna ashtakavarga heatmap with bindu scores",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/ashtakavarga",docsSummary:"Sarva, Bhinna, and Shodhya Pinda views in a tabbed heatmap",topic:"Vedic"},{pascal:"RoxyShadbalaTable",tag:"roxy-shadbala-table",slug:"shadbala-table",heading:"Shadbala",description:"Six-fold planetary strength with adequacy badge per planet",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/shadbala",docsSummary:"Six-fold planetary strength bar plus rupas and adequacy badge",topic:"Vedic"},{pascal:"RoxyDashaTimeline",tag:"roxy-dasha-timeline",slug:"dasha-timeline",heading:"Vimshottari dasha",description:"Vimshottari dasha timeline with active mahadasha highlighted",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/dasha/{current,major,sub/...}",docsSummary:"Vimshottari mahadasha + antardasha + pratyantardasha",topic:"Vedic"},{pascal:"RoxyGunaMilan",tag:"roxy-guna-milan",slug:"guna-milan",heading:"Guna milan",description:"36-point Ashtakoota matrimonial compatibility breakdown",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/compatibility",docsSummary:"36-point Ashtakoota with eight sub-scores",topic:"Vedic"},{pascal:"RoxyPanchangTable",tag:"roxy-panchang-table",slug:"panchang-table",heading:"Panchang",description:"Panchang muhurta table with auspicious and inauspicious periods",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/panchang/{basic,detailed}",docsSummary:"15+ muhurtas in detailed mode",topic:"Vedic"},{pascal:"RoxyChoghadiyaGrid",tag:"roxy-choghadiya-grid",slug:"choghadiya-grid",heading:"Choghadiya",description:"Day and night Choghadiya muhurta tiles for activity timing",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/panchang/choghadiya",docsSummary:"Day and night Choghadiya muhurta tiles colored by effect",topic:"Vedic"},{pascal:"RoxyYogaList",tag:"roxy-yoga-list",slug:"yoga-list",heading:"Yoga catalog",description:"Yoga reference cards from the catalog with optional detail mode",docsLabel:"Vedic",endpointLabel:"GET /vedic-astrology/yoga, /yoga/{id}",docsSummary:"Filterable yoga cards from the 300 plus yoga catalog",topic:"Vedic"},{pascal:"RoxyNakshatraCard",tag:"roxy-nakshatra-card",slug:"nakshatra-card",heading:"Nakshatra",description:"Nakshatra reference card with lord, deity, symbol, characteristics, and remedies",docsLabel:"Vedic",endpointLabel:"GET /vedic-astrology/nakshatras/{id}",docsSummary:"Lord, deity, symbol, characteristics, remedies",topic:"Vedic"},{pascal:"RoxyDoshaCard",tag:"roxy-dosha-card",slug:"dosha-card",heading:"Manglik dosha",description:"Manglik, Kaal Sarp, or Sade Sati presence card",docsLabel:"Vedic",endpointLabel:"POST /vedic-astrology/dosha/{manglik,kalsarpa,sadhesati}",docsSummary:"Presence, severity, remedies, scoped effects",topic:"Vedic"},{pascal:"RoxyNumerologyCard",tag:"roxy-numerology-card",slug:"numerology-card",heading:"Life path number",description:"Numerology card for life path, expression, personal year, or full chart",docsLabel:"Numerology",endpointLabel:"POST /numerology/{life-path,expression,personal-year,chart}",docsSummary:"Life path, expression, personal year, full chart",topic:"Numerology"},{pascal:"RoxyTarotCard",tag:"roxy-tarot-card",slug:"tarot-card",heading:"Daily tarot card",description:"Single tarot card with upright/reversed flip animation",docsLabel:"Tarot",endpointLabel:"GET /tarot/cards/{id}, POST /tarot/daily",docsSummary:"Single card with upright and reversed flip",topic:"Tarot"},{pascal:"RoxyTarotSpread",tag:"roxy-tarot-spread",slug:"tarot-spread",heading:"Three-card spread",description:"Tarot spread renderer for three-card, Celtic Cross, love, or yes/no",docsLabel:"Tarot",endpointLabel:"POST /tarot/spreads/{three-card,celtic-cross,love}, /tarot/yes-no, /tarot/draw",docsSummary:"Spreads with positions and reading",topic:"Tarot"},{pascal:"RoxyBiorhythmChart",tag:"roxy-biorhythm-chart",slug:"biorhythm-chart",heading:"Daily biorhythm",description:"Daily biorhythm bars or multi-day forecast cycle lines",docsLabel:"Biorhythm",endpointLabel:"POST /biorhythm/{daily,forecast,critical-days}",docsSummary:"Daily bars, forecast cycle lines, critical days",topic:"Biorhythm"},{pascal:"RoxyHexagram",tag:"roxy-hexagram",slug:"hexagram",heading:"I Ching hexagram",description:"I Ching hexagram with trigram glyphs, judgment, image, and changing lines",docsLabel:"I Ching",endpointLabel:"GET /iching/hexagrams/{number}, /iching/cast, POST /iching/daily, /iching/daily/cast",docsSummary:"Hexagram with trigrams, judgment, image, changing lines",topic:"I Ching"},{pascal:"RoxyEndpointForm",tag:"roxy-endpoint-form",slug:"endpoint-form",heading:"Schema-driven form",description:"Schema-driven form that emits roxy-submit with a validated payload",docsLabel:"Helper",endpointLabel:"Any endpoint via x-roxy-ui hints",docsSummary:"Schema-driven form, emits roxy-submit",topic:"Helpers",selfFetching:!0},{pascal:"RoxyLocationSearch",tag:"roxy-location-search",slug:"location-search",heading:"City search",description:"City search input with debounced /location/search calls",docsLabel:"Helper",endpointLabel:"GET /location/search",docsSummary:"Debounced city search input, emits roxy-location-select",topic:"Helpers",selfFetching:!0},{pascal:"RoxyData",tag:"roxy-data",slug:"data",heading:"Generic renderer",description:"Generic fallback renderer for any OpenAPI response shape",docsLabel:"Helper",endpointLabel:"Any response shape",docsSummary:"Generic fallback renderer for unknown shapes",topic:"Helpers",selfFetching:!0}];var pr="0.5.0";var ya=ct.map(n=>n.slug);return yr(xa);})();
/*! Bundled license information:

@lit/reactive-element/css-tag.js:
  (**
   * @license
   * Copyright 2019 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/reactive-element.js:
lit-html/lit-html.js:
lit-element/lit-element.js:
@lit/reactive-element/decorators/custom-element.js:
@lit/reactive-element/decorators/property.js:
@lit/reactive-element/decorators/state.js:
@lit/reactive-element/decorators/event-options.js:
@lit/reactive-element/decorators/base.js:
@lit/reactive-element/decorators/query.js:
@lit/reactive-element/decorators/query-all.js:
@lit/reactive-element/decorators/query-async.js:
@lit/reactive-element/decorators/query-assigned-nodes.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

lit-html/is-server.js:
  (**
   * @license
   * Copyright 2022 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/query-assigned-elements.js:
  (**
   * @license
   * Copyright 2021 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)
*/
//# sourceMappingURL=roxy-ui.js.map
