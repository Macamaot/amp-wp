!function(e){var t={};function a(n){if(t[n])return t[n].exports;var i=t[n]={i:n,l:!1,exports:{}};return e[n].call(i.exports,i,i.exports,a),i.l=!0,i.exports}a.m=e,a.c=t,a.d=function(e,t,n){a.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},a.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},a.t=function(e,t){if(1&t&&(e=a(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(a.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)a.d(n,i,function(t){return e[t]}.bind(null,i));return n},a.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return a.d(t,"a",t),t},a.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},a.p="",a(a.s=9)}({9:function(e,t){window.ampCustomizeControls=function(e,t){"use strict";var a={data:{queryVar:"amp",panelId:"",ampUrl:"",l10n:{unavailableMessage:"",unavailableLinkText:""}},tooltipTimeout:5e3,tooltipVisible:new e.Value(!1),tooltipFocused:new e.Value(0),boot:function(t){function n(){e.panel(a.data.panelId,a.panelReady)}a.data=t,e.state?(a.addState(),e.bind("ready",n)):e.bind("ready",(function(){a.addState(),n()}))},addState:function(){e.state.add("ampEnabled",new e.Value(!1)),e.state.add("ampAvailable",new e.Value(!1))},isAmpUrl:function(e){var t=document.createElement("a"),n=new RegExp("\\/"+a.data.queryVar+"\\/?$");return t.href=e,!_.isUndefined(wp.customize.utils.parseQueryString(t.search.substr(1))[a.data.queryVar])||n.test(t.pathname)},unampifyUrl:function(e){var n=document.createElement("a"),i=new RegExp("\\/"+a.data.queryVar+"\\/?$");if(n.href=e,n.pathname=n.pathname.replace(i,""),1<n.search.length){var o=window.wp.customize.utils.parseQueryString(n.search.substr(1));delete o[a.data.queryVar],n.search=t.param(o)}return n.href},ampifyUrl:function(e){var t=document.createElement("a");return t.href=a.unampifyUrl(e),t.search.length&&(t.search+="&"),t.search+=a.data.queryVar+"=1",t.href},tryToCloseTooltip:function(){clearTimeout(a.tooltipTimeoutId),a.tooltipTimeoutId=setTimeout((function(){a.tooltipVisible.get()&&(0<a.tooltipFocused.get()?a.tryToCloseTooltip():a.tooltipVisible.set(!1))}),a.tooltipTimeout)},setCurrentAmpUrl:function(t){var n=e.state("ampEnabled").get();return!n&&a.isAmpUrl(t)?a.unampifyUrl(t):n&&!a.isAmpUrl(t)?a.ampifyUrl(t):t},updatePreviewUrl:function(){e.previewer.previewUrl.set(a.setCurrentAmpUrl(e.previewer.previewUrl.get()))},enableAndNavigateToUrl:function(t){e.state("ampEnabled").set(!0),e.previewer.previewUrl.set(t)},updatePanelNotifications:function(){var t=e.panel(a.data.panelId),n=t.sections().concat([t]);e.state("ampAvailable").get()?_.each(n,(function(e){e.notifications.remove("amp_unavailable")})):_.each(n,(function(t){t.notifications.add(new e.Notification("amp_unavailable",{message:a.data.l10n.unavailableMessage,type:"info",linkText:a.data.l10n.unavailableLinkText,url:a.data.ampUrl,templateId:"customize-amp-unavailable-notification",render:function(){var t=e.Notification.prototype.render.call(this);return t.find("a").on("click",(function(e){e.preventDefault(),a.enableAndNavigateToUrl(this.href)})),t}}))}))},panelReady:function(n){var i,o=t(wp.template("customize-amp-enabled-toggle")({message:a.data.l10n.unavailableMessage,linkText:a.data.l10n.unavailableLinkText,url:a.data.ampUrl})),l=o.find("input[type=checkbox]"),r=o.find(".tooltip"),u=r.find("a");n.expanded.bind((function(t){t&&(e.state("ampAvailable").get()?e.state("ampEnabled").set(n.expanded.get()):n.notifications||setTimeout((function(){a.tooltipVisible.set(!0)}),250))})),n.notifications&&(e.state("ampAvailable").bind(a.updatePanelNotifications),a.updatePanelNotifications(),e.section.bind("add",a.updatePanelNotifications)),e.previewedDevice.bind((function(t){e.state("ampAvailable").get()&&e.state("ampEnabled").set("mobile"===t)})),e.previewer.bind("amp-status",(function(t){e.state("ampAvailable").set(t.available)})),e.previewer.bind("amp-status",(function t(a){e.state("ampEnabled").set(a.enabled),e.previewer.unbind("amp-status",t)})),e.previewer.previewUrl.validate=(i=e.previewer.previewUrl.validate,function(e){var t=i.call(this,e);return t&&(t=a.setCurrentAmpUrl(t)),t}),e.state("ampEnabled").bind((function(e){l.prop("checked",e),a.updatePreviewUrl()})),e.state("ampAvailable").bind((function(t){l.toggleClass("disabled",!t),e.state("ampEnabled").get()&&a.tooltipVisible.set(!t)})),t(".devices-wrapper").before(o),u.on("click",(function(e){e.preventDefault(),a.enableAndNavigateToUrl(this.href)})),a.tooltipVisible.bind((function(e){r.attr("aria-hidden",e?"false":"true"),e?(t(document).on("click.amp-toggle-outside",(function(e){t.contains(o[0],e.target)||a.tooltipVisible.set(!1)})),r.fadeIn(),a.tryToCloseTooltip()):(r.fadeOut(),a.tooltipFocused.set(0),t(document).off("click.amp-toggle-outside"))})),l.on("click",(function(){this.checked=!this.checked,e.state("ampAvailable").get()?e.state("ampEnabled").set(!e.state("ampEnabled").get()):a.tooltipVisible.set(!0)})),r.on("mouseenter",(function(){e.state("ampAvailable").get()||a.tooltipVisible.set(!0),a.tooltipFocused.set(a.tooltipFocused.get()+1)})),r.on("mouseleave",(function(){a.tooltipFocused.set(a.tooltipFocused.get()-1)})),u.on("focus",(function(){e.state("ampAvailable").get()||a.tooltipVisible.set(!0),a.tooltipFocused.set(a.tooltipFocused.get()+1)})),u.on("blur",(function(){a.tooltipFocused.set(a.tooltipFocused.get()-1)}))}};return a}(wp.customize,jQuery)}});