webpackJsonp([37],{"1e4q":function(t,e,a){"use strict";e.a={data:function(){return{time:"",template:{},baseInfo:{},data:[]}},methods:{goBack:function(){this.$emit("flags","1")}},watch:{},mounted:function(){this.baseInfo=JSON.parse(sessionStorage.getItem("baseInfo")),this.template=JSON.parse(sessionStorage.getItem("templateInfo")),this.time=new Date(1e3*this.template.time).toLocaleString(),this.data=this.template.keyword_name_list.split(",")}}},"1jc3":function(t,e,a){e=t.exports=a("BkJT")(!0),e.push([t.i,"strong[data-v-557529c5]{font-weight:400}.font[data-v-557529c5]{color:#b4b4b5;margin-right:.2rem;display:inline-block;width:1rem}.line[data-v-557529c5]{margin:.15rem 0}.content[data-v-557529c5]{padding:.2rem;display:-webkit-box;display:-ms-flexbox;display:flex;margin-top:.2rem}.left[data-v-557529c5]{width:3.2rem;border:1px solid #f2f2f2}.left_h[data-v-557529c5]{padding:.15rem .1rem;border-bottom:1px solid #f2f2f2}.left_c[data-v-557529c5]{padding:.1rem;min-height:2.3rem}.left_c>header[data-v-557529c5]{margin-bottom:.2rem}.left_f[data-v-557529c5]{padding:.1rem;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between}.goBack[data-v-557529c5]{position:relative;left:12rem;cursor:pointer}","",{version:3,sources:["C:/Users/Administrator.SKY-20120726ABV/Desktop/template-ed03e4c3a0dc8b64a9d7fd9466d212e71780cb9c-ed03e4c3a0dc8b64a9d7fd9466d212e71780cb9c/templates/src/components/store/template/templateInfo.vue"],names:[],mappings:"AACA,wBAA0B,eAAiB,CAC1C,AACD,uBAAyB,cAAe,mBAAqB,qBAAsB,UAAW,CAC7F,AACD,uBAAuB,eAAiB,CACvC,AACD,0BAA4B,cAAgB,AAAC,oBAAqB,AAAC,oBAAqB,AAAC,aAAc,gBAAmB,CACzH,AACD,uBACE,aAAc,AAEd,wBAA0B,CAC3B,AACD,yBACE,qBAAwB,AACxB,+BAAiC,CAClC,AACD,yBACE,cAAgB,AAChB,iBAAmB,CACpB,AACD,gCACE,mBAAsB,CACvB,AACD,yBACE,cAAgB,AAChB,oBAAqB,AACrB,oBAAqB,AACrB,aAAc,AACd,yBAA0B,AACtB,sBAAuB,AACnB,mBAAoB,AAC5B,yBAA0B,AACtB,sBAAuB,AACnB,6BAA+B,CACxC,AACD,yBACE,kBAAmB,AACnB,WAAY,AACZ,cAAgB,CACjB",file:"templateInfo.vue",sourcesContent:["\nstrong[data-v-557529c5]{  font-weight: 400;\n}\n.font[data-v-557529c5]{  color:#b4b4b5 ;margin-right: 0.2rem;display: inline-block;width: 1rem\n}\n.line[data-v-557529c5]{margin: 0.15rem 0\n}\n.content[data-v-557529c5]{  padding: 0.2rem; display: -webkit-box; display: -ms-flexbox; display: flex;margin-top: 0.2rem;\n}\n.left[data-v-557529c5]{\n  width: 3.2rem;\n  /*padding: 0.1rem;*/\n  border: 1px solid #f2f2f2;\n}\n.left_h[data-v-557529c5]{\n  padding: 0.15rem 0.1rem;\n  border-bottom: 1px solid #f2f2f2;\n}\n.left_c[data-v-557529c5]{\n  padding: 0.1rem;\n  min-height: 2.3rem;\n}\n.left_c>header[data-v-557529c5]{\n  margin-bottom: 0.2rem;\n}\n.left_f[data-v-557529c5]{\n  padding: 0.1rem;\n  display: -webkit-box;\n  display: -ms-flexbox;\n  display: flex;\n  -webkit-box-align: center;\n      -ms-flex-align: center;\n          align-items: center;\n  -webkit-box-pack: justify;\n      -ms-flex-pack: justify;\n          justify-content: space-between;\n}\n.goBack[data-v-557529c5]{\n  position: relative;\n  left: 12rem;\n  cursor: pointer;\n}\n"],sourceRoot:""}])},ARTH:function(t,e,a){"use strict";function s(t){a("TJhA")}Object.defineProperty(e,"__esModule",{value:!0});var n=a("1e4q"),i=a("z32Y"),A=a("o7Pn"),o=s,c=A(n.a,i.a,o,"data-v-557529c5",null);e.default=c.exports},TJhA:function(t,e,a){var s=a("1jc3");"string"==typeof s&&(s=[[t.i,s,""]]),s.locals&&(t.exports=s.locals);a("8bSs")("55e39c60",s,!0)},z32Y:function(t,e,a){"use strict";var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"info"},[a("div",{staticClass:"bread"},[a("a",{staticStyle:{"font-size":"0.14rem",color:"#b4b4b5"},attrs:{href:"javascript:void(0)"},on:{click:t.goBack}},[t._v("我的模板")]),t._v(" "),a("span",{staticStyle:{color:"#b4b4b5"}},[t._v("/")]),t._v(" "),a("span",[t._v("模板详情")]),t._v(" "),a("span",{staticClass:"goBack",on:{click:t.goBack}},[t._v("返回")])]),t._v(" "),a("div",{staticClass:"content"},[a("section",{staticStyle:{width:"3.2rem","margin-right":"0.2rem"}},[a("div",{staticClass:"left"},[a("div",{staticClass:"left_h"},[a("img",{staticStyle:{width:"50px","max-height":"100%","border-radius":"50%"},attrs:{src:t.baseInfo.pic,alt:""}})]),t._v(" "),a("div",{staticClass:"left_c"},[a("header",[a("p",[t._v(t._s(t.template.title))]),t._v(" "),a("time",{staticClass:"font",staticStyle:{width:"auto"}},[t._v(t._s(t.time))])]),t._v(" "),t._l(t.data,function(e){return a("p",[a("span",{staticClass:"font"},[t._v(t._s(e))]),t._v(" "),a("strong",[t._v("咖啡")])])})],2),t._v(" "),a("div",{staticClass:"left_f"},[a("p",{staticClass:"font"},[t._v("查看详情")]),t._v(" "),a("Icon",{attrs:{type:"chevron-right",color:"#b4b4b5"}})],1)]),t._v(" "),a("p",{staticClass:"font",staticStyle:{"text-align":"center",margin:"0.2rem 0",width:"100%"}},[t._v("内容实例")])]),t._v(" "),a("section",{staticClass:"right"},[a("p",{staticClass:"line"},[a("span",{staticClass:"font"},[t._v("模板ID")]),t._v(" "),a("strong",[t._v(t._s(t.template.template_id))])]),t._v(" "),a("p",{staticClass:"line"},[a("span",{staticClass:"font"},[t._v("标题")]),t._v(" "),a("strong",[t._v(t._s(t.template.title))])]),t._v(" "),a("div",{staticClass:"line",staticStyle:{display:"flex"}},[a("span",{staticClass:"font"},[t._v("关键词")]),t._v(" "),a("div",t._l(t.data,function(e){return a("p",[a("span",{staticClass:"font"},[t._v(t._s(e))]),t._v(" "),a("strong",[t._v("keyword1.DATA")])])}))])])])])},n=[],i={render:s,staticRenderFns:n};e.a=i}});
//# sourceMappingURL=37.2a548f2c458dbe175c7b.js.map