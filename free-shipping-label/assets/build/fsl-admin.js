(()=>{var e={521:()=>{jQuery(document).ready((function(){const e=jQuery,t={group:{}},n="dvnt-active",a="nav-tab-active",s=e(".devnet-plugin-settings-page");if(!s.length)return!1;const i=s.data("id");e(".wp-color-picker-field").wpColorPicker();const o=i?`${i}_activetab`:"activetab",r=i?`${i}_activetab_inner`:"activetab";e(".group").hide();let l="",d="";"undefined"!=typeof localStorage&&(l=localStorage.getItem(o),d=localStorage.getItem(r)),window.location.hash&&(l=window.location.hash,"undefined"!=typeof localStorage&&localStorage.setItem(o,l)),l&&e(l+"-tab").length?e(l+"-tab").addClass("nav-tab-active"):e(".nav-tab-wrapper a:first").addClass("nav-tab-active"),l&&e(l).length?(e(l).addClass(n).fadeIn(),d&&e(`${l} ${d}-tab`).length?(e(`${l} ${d}-tab`).addClass(a),e(`${l} ${d}`).addClass(n).fadeIn()):(e(`${l} .inner-tab:first`).addClass(a),e(`${l} .group:first`).addClass(n).fadeIn())):e(".group:first").addClass(n).fadeIn(),e(document).on("click",".nav-tab-wrapper a",(function(t){const s=e(this),i=s.hasClass("has-tabs"),l=s.hasClass("inner-tab"),d=s.attr("href");if(s.hasClass(a))return!1;if(l){"undefined"!=typeof localStorage&&localStorage.setItem(r,d);const e=s.closest(".has-tabs");e.find(".nav-tab-wrapper a").removeClass(a),s.addClass(a).blur(),e.find(".group").hide().removeClass(n),e.find(d).addClass(n).fadeIn()}else"undefined"!=typeof localStorage&&localStorage.setItem(o,d),s.addClass(a).blur(),e(".group").hide().removeClass(n),e(d).addClass(n).fadeIn(),i?(e(".nav-tab-wrapper a").not(".has-tabs").not(".inner-tab").removeClass(a),e(`${d} .inner-tab:first`).addClass(a),e(`${d} .group:first`).addClass(n),e(`${d} .group:first`).fadeIn()):(e(".nav-tab-wrapper a").removeClass(a),s.addClass(a));t.preventDefault()})),e(".group .collapsed").each((function(){e(this).find("input:checked").parent().parent().parent().nextAll().each((function(){if(e(this).hasClass("last"))return e(this).removeClass("hidden"),!1;e(this).filter(".hidden").removeClass("hidden")}))})),e(document).on("click",".dvnt-f-browse",(function(t){t.preventDefault();const n=e(this),a=wp.media.frames.file_frame=wp.media({title:n.data("uploader_title"),button:{text:n.data("uploader_button_text")},multiple:!1});a.on("select",(function(){attachment=a.state().get("selection").first().toJSON(),n.prev(".dvnt-f-url").val(attachment.url).change(),"image"===attachment.type&&n.parent().find(".dvnt-f-preview").attr("src",attachment.url)})),a.open()}));e("tr.info").map(((t,n)=>{const a=e(n),s=a.find("label").text(),i=a.find(".info-description").html();a.html(`\n\t\t<td colspan="2">\n\t\t\t<span class="info-title">${s}</span>\n\t\t\t<span class="info-description">${i}</p>\n\t\t</td>\n\t\t`)})),e(".info-description input").on("click",(function(){e(this).select()}));e(".dvnt-groups").data("repeatable");const c=(e,t)=>e.replace(/(\[\d+])/,"["+t+"]");function h(t){t.closest(".dvnt-groups").find(".dvnt-group").each((function(t){e(this).find("[name], [id], [for]").each((function(){const n=e(this),a=n.attr("name"),s=n.attr("id"),i=n.attr("for");a&&n.attr("name",c(a,t)),s&&n.attr("id",c(s,t)),i&&n.attr("for",c(i,t))}))}))}e(document).on("click",".dvnt-repeat-group",(function(n){n.preventDefault();let a=e(this).closest(".dvnt-groups").find(".dvnt-group").first().clone();a.length||(a=t.group.show());const s=e(".dvnt-group").length;a.find("[name], [id], [for]").each((function(){const t=e(this),n=t.attr("name"),a=t.attr("id"),i=t.attr("for"),o=t.attr("checked");if(n&&t.attr("name",c(n,s)),a&&t.attr("id",c(a,s)),i&&t.attr("for",c(i,s)),void 0!==this.value&&"SELECT"!==this.nodeName&&(e(this).val(""),e(this).attr("value","")),o&&(t.prop("checked",!1),e(this).val("1")),t.hasClass("select2-hidden-accessible")){t.empty();t.parent().children().each((function(){const t=e(this).prop("tagName").toLowerCase();"select"!==t&&"label"!==t&&e(this).remove()}))}})),a.find(".dvnt-f-preview").attr("src",""),e(this).before(a),h(e(this))})),e(document).on("click",".dvnt-remove-group",(function(n){if(n.preventDefault(),confirm("Are you sure?")){const n=e(this),a=n.parent(".dvnt-group");t.group=a,a.fadeOut((function(){a.remove()})),h(n)}}));const f="attach-units-to--",_=e(`[class^="${f}"]`).get();_.length&&_.forEach((t=>{const n=e(t),a=n.attr("class").split(/\s+/),s=n.find("select");a.forEach((t=>{if(t.startsWith(f)){const a=t.substring(17),i=e(`tr.${a}`);if(i.length&&s.length){i.addClass("has-unit-selector");const e=s.detach();i.find("input").after(e),n.remove()}}}))}))}))}},t={};function n(a){var s=t[a];if(void 0!==s)return s.exports;var i=t[a]={exports:{}};return e[a](i,i.exports,n),i.exports}n.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return n.d(t,{a:t}),t},n.d=(e,t)=>{for(var a in t)n.o(t,a)&&!n.o(e,a)&&Object.defineProperty(e,a,{enumerable:!0,get:t[a]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";n(521);jQuery(document).ready((function(){const e=jQuery,{ajaxurl:t}=devnet_fsl_admin_ajax,n=(t,n)=>{const a=t.val();if(a){const t=a.split("-");n.get().forEach(((n,a)=>{const s=e(n).find("th label"),i=s.find("span"),o=t[a].toUpperCase(),r=`<span class="devnet-text-change">${o}</span>`;i.length?i.replaceWith(r):s.append(`<span>${o}</span>`)}))}};function a(t,n,a){const s=t.closest(".group").attr("id"),i=e(`#${s}`),o=n.closest("tr"),r=i.find("tr.circle_size"),l=i.find("tr.icon"),d=i.find("tr.icon_color"),c=i.find("tr.circle_bg_color"),h=i.find("tr.bar_border_color"),f=i.find("tr.bar_height"),_=i.find("tr.bar_radius"),u=i.find("tr.remove_bar_stripes"),p=i.find("tr.disable_animation"),m=i.find("tr.disabled_animations"),v=i.find("tr.indicator_icon"),g=i.find("tr.indicator_icon_size"),b=i.find("tr.indicator_icon_shape"),w=i.find("tr.indicator_icon_bg_color");"circular"===a?(r.show(),o.show(),c.show(),h.hide(),f.hide(),_.hide(),u.hide(),p.hide(),m.hide(),v.hide(),g.hide(),b.hide(),w.hide(),"icon"===n.val()?(l.show(),l.removeClass("hide-option-row"),d.show(),d.removeClass("hide-option-row")):(l.hide(),d.hide())):(r.hide(),o.hide(),c.hide(),l.hide(),d.hide(),h.show(),f.show(),_.show(),u.show(),p.show(),m.show(),v.show(),b.show(),e(`[name="${s}[indicator_icon]"]`).is(":checked")?(g.show(),w.show(),l.show(),d.show()):(g.hide(),w.hide()))}function s(e,t){if("circular"!==e.closest("table").find(".bar_type select").val())return!1;const n=e.closest("tr").next(".icon"),a=n.next(".icon_color");"icon"===t?(a.show(),n.show()):(a.hide(),n.hide())}function i(t,n,a=!0){const s=e(t);function i(e){const t=e.parent(".dvnt-group-field").length?e.parent(".dvnt-group-field"):e.closest("tr");a?e.prop("disabled",s.is(":checked")):s.is(":checked")?(t.show(),t.removeClass("hide-option-row")):(t.hide(),t.addClass("hide-option-row"))}(Array.isArray(n)?n:[n]).forEach((t=>{const n=e(t);i(n),s.on("change",(()=>i(n)))}))}function o(t){const n=e(`[name="${t}[inherit_progress_bar_settings]"]`),a=n.is(":checked");return n.length&&a}function r(t,n){const a=e(`[name="${t}[bar_type]"]`).val();return n?n===a:a}e('[name^="devnet_fsl_"][name$="[inherit_progress_bar_settings]"]').each((function(){const t=e(this).closest("tr.inherit_progress_bar_settings"),n=this.name.replace("[inherit_progress_bar_settings]",""),i=e(`[name="${n}[bar_type]"]`),o=e(`[name="${n}[inside_circle]"]`);e(this).is(":checked")&&t.nextAll().hide(),e(this).on("change",(function(){e(this).is(":checked")?t.nextAll().hide():(t.nextAll().show(),a(i,o,i.val()),s(o,o.val()))}))})),e('[name^="devnet_fsl_"][name$="[bar_type]"]').each((function(){const t=e(this),n=this.name.replace("[bar_type]",""),s=e(`[name="${n}[inside_circle]"]`);o(n)||a(t,s,t.val()),t.on("change",(function(){a(t,s,this.value)}))})),e('[name^="devnet_fsl_"][name$="[indicator_icon]"]').each((function(){const e=this.name.replace("[indicator_icon]","");!o(e)&&r(e,"linear")&&i(`[name="${this.name}"]`,[`[name="${e}[indicator_icon_size]"]`,`[name="${e}[indicator_icon_shape]"]`,`[name="${e}[indicator_icon_bg_color]"]`,`[name="${e}[icon]"]`,`[name="${e}[icon_color]"]`],!1)})),e('[name^="devnet_fsl_"][name$="[inside_circle]"]').each((function(){const t=e(this),n=this.name.replace("[inside_circle]",""),a=e(`[name="${n}[bar_type]"]`);o(n)||s(t,t.val()),t.on("change",(function(){s(t,this.value,a.val())}))})),e('[name^="devnet_fsl_"][name$="[disable_animation]"]').each((function(){const e=this.name.replace("[disable_animation]","");!o(e)&&r(e,"linear")&&i(`[name="${this.name}"]`,`[name="${this.name.replace("[disable_animation]","[disabled_animations]")}"]`,!1)}));const l=e('[name="devnet_fsl_general[custom_threshold]"]'),d=e('[name^="devnet_fsl_general\\[custom_threshold_per_method\\]"]'),c=l.val(),h=d.filter((function(){return""!==e(this).val()})).length>0;c&&0!=c||h||(l.addClass("disabled"),d.addClass("disabled")),e(document).on("change",'[name="devnet_fsl_general[enable_custom_threshold]"]',(function(){e(this).is(":checked")?(l.removeClass("disabled"),d.removeClass("disabled")):(l.addClass("disabled"),l.attr("value",""),d.addClass("disabled"),d.attr("value",""))}));const f=e('[name="devnet_fsl_general[multilingual]"]'),_=e('\n\tinput[type="text"][name^="devnet_fsl_"][name$="[title]"], \n\tinput[type="text"][name^="devnet_fsl_"][name$="[description]"], \n\tinput[type="text"][name^="devnet_fsl_"][name$="[qualified_message]"], \n\tinput[type="text"][name^="devnet_fsl_"][name$="[text]"],\n\tinput[type="text"][name^="devnet_fsl_"][name$="[label]"]\n\t');f.is(":checked")?_.addClass("disabled multilingual"):_.removeClass("disabled multilingual"),_.each((function(){e(this).hasClass("disabled multilingual")&&e(this).after('<div class="devnet-visible-tooltip">Multilingual option is active!</div>')})),e('tr[class^="shortcode_info"] input').replaceWith('<input type="text" class="fsl-shortcode" value=\'[fsl-progress-bar]\' readonly>'),e(document).on("click","input.fsl-shortcode",(function(t){e(this).select(),e(this)[0].setSelectionRange(0,e(this).val().length)}));const u=e('[name="devnet_fsl_gift_bar[gift_product]"]');"function"==typeof e.fn.select2&&u.select2({ajax:{url:t,dataType:"json",delay:250,data:e=>({q:e.term,action:"devnet_fsl_search",searchIn:["product","product_variation"]}),processResults(t){const n=[];return t&&e.each(t,(function(e,t){n.push({id:`${t[2]}---${t[0]}___${t[1]}`,text:t[1],selected:!0})})),{results:n}},cache:!0},minimumInputLength:3});const p=e('[name="devnet_fsl_notice_bar[position]"]'),m=e("#devnet_fsl_notice_bar tr.margin_y, #devnet_fsl_notice_bar tr.margin_x");n(p,m),e(p).on("change",(function(t){n(e(this),m)}));const v=e('[id="devnet_fsl_label[exclude]"]');"function"==typeof e.fn.select2&&v.select2({ajax:{url:t,dataType:"json",delay:250,data:e=>({q:e.term,action:"devnet_fsl_search",searchIn:["product","category"]}),processResults(t){const n=[];return t&&e.each(t,(function(e,t){n.push({id:`${t[2]}---${t[0]}___${t[1]}`,text:t[1],selected:!0})})),{results:n}},cache:!0},minimumInputLength:3});const g=e('[name="devnet_fsl_label[label_over_image]"]'),b=["#devnet_fsl_label tr.position","#devnet_fsl_label tr.margin_y","#devnet_fsl_label tr.margin_x"];g.is(":checked")?b.forEach((t=>{e(t).show()})):b.forEach((t=>{e(t).hide()})),e(g).on("change",(function(){e(this).is(":checked")?b.forEach((t=>{e(t).show()})):b.forEach((t=>{e(t).hide()}))}));const w=e('[name="devnet_fsl_label[position]"]'),$=e("#devnet_fsl_label tr.margin_y, #devnet_fsl_label tr.margin_x");n(w,$),e(w).on("change",(function(t){n(e(this),$)}));const C=["#devnet_fsl_label tr.text","#devnet_fsl_label tr.text_color","#devnet_fsl_label tr.bg_color","#devnet_fsl_label tr.hide_border_shadow"],y=e('[name="devnet_fsl_label[enable_image_label]"]'),x=e("#devnet_fsl_label tr.image"),k=e("#devnet_fsl_label tr.image_width");y.is(":checked")?(x.show(),k.show(),C.forEach((t=>{e(t).addClass("disabled")}))):(x.hide(),k.hide(),C.forEach((t=>{e(t).removeClass("disabled")}))),e(y).on("change",(function(){y.is(":checked")?(x.show(),k.show(),C.forEach((t=>{e(t).addClass("disabled")}))):(x.hide(),k.hide(),C.forEach((t=>{e(t).removeClass("disabled")})))}))}))})()})();