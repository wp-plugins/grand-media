/**
 * TermBox v0.9.1
 */
function gm_array_unique_noempty(a) {
	var out = [];
	jQuery.each(a, function (key, val) {
		val = jQuery.trim(val);
		if (val && jQuery.inArray(val, out) == -1) {
			out.push(val);
		}
	});
	return out;
}
var gmTagBox;
(function ($) {
	gmTagBox = {
		clean      : function (tags) {
			return tags.replace(/\s*,\s*/g, ",").replace(/,+/g, ",").replace(/[,\s]+$/, "").replace(/^[,\s]+/, "");
		},
		parseTags  : function (el) {
			var id = el.id,
					num = id.split("-check-num-")[1],
					taxbox = $(el).closest(".tagsdiv"),
					thetags = taxbox.find(".the-tags"),
					current_tags = thetags.val().split(","),
					new_tags = [];
			delete current_tags[num];
			$.each(current_tags, function (key, val) {
				val = $.trim(val);
				if (val) {
					new_tags.push(val);
				}
			});
			thetags.val(this.clean(new_tags.join(",")));
			this.quickClicks(taxbox);
			return false;
		},
		quickClicks: function (el) {
			var thetags = $(".the-tags", el),
					tagchecklist = $(".tagchecklist", el),
					id = $(el).attr("id"),
					current_tags, disabled;
			if (!thetags.length) {
				return;
			}
			disabled = thetags.prop("disabled");
			current_tags = thetags.val().split(",");
			tagchecklist.empty();
			$.each(current_tags, function (key, val) {
				var span, xbutton;
				val = $.trim(val);
				if (!val) {
					return;
				}
				span = $("<span />").text(val);
				if (!disabled) {
					xbutton = $('<a id="' + id + "-check-num-" + key + '" class="ntdelbutton">X</a>');
					xbutton.click(function () {
						gmTagBox.parseTags(this);
					});
					span.prepend("&nbsp;").prepend(xbutton);
				}
				tagchecklist.append(span);
			});
		},
		flushTags  : function (el, a, f) {
			a = a || false;
			var text, tags = $(".the-tags", el),
					newtag = $("input.newtag", el),
					newtags;
			text = a ? $(a).text() : newtag.val();
			var tagsval = tags.val();
			newtags = tagsval ? tagsval + "," + text : text;
			newtags = this.clean(newtags);
			newtags = gm_array_unique_noempty(newtags.split(",")).join(",");
			tags.val(newtags);
			this.quickClicks(el);
			if (!a) {
				newtag.val("");
			}
			if ("undefined" == typeof(f)) {
				newtag.focus();
			}
			return false;
		},
		init       : function () {
			var t = this,
					ajaxtag = $("div.ajaxtag");
			$(".tagsdiv").each(function () {
				gmTagBox.quickClicks(this);
			});
			$("input.tagadd", ajaxtag).click(function () {
				t.flushTags($(this).closest(".tagsdiv"));
			});
			$("div.taghint", ajaxtag).click(function () {
				$(this).css("visibility", "hidden").parent().siblings(".newtag").focus();
			});
			$("input.newtag", ajaxtag).blur(function () {
				if (this.value == "") {
				}
			}).focus(function () {
					}).keyup(function (e) {
						if (13 == e.which) {
							gmTagBox.flushTags($(this).closest(".tagsdiv"));
							return false;
						}
					}).keypress(function (e) {
						if (13 == e.which) {
							e.preventDefault();
							return false;
						}
					}).each(function () {
						var tax = $(this).closest("div.tagsdiv").attr("id");
						//noinspection JSUnresolvedVariable
						$(this).suggest(ajaxurl + "?_wpnonce=" + gMediaTermBox.nonce + "&action=gmDoAjax&task=term-search&tax=" + tax, {
							delay      : 500,
							minchars   : 2,
							multiple   : true,
							multipleSep: ","
						});
					});
			$(".gmAddMedia").on('click', "#tagcloud-gmedia_tag span", function () {
				gmTagBox.flushTags($(this).closest("#termsdiv-gmedia_tag").children(".tagsdiv"), this);
				return false;
			});
			$(".gmAddMedia").on('mousedown', ".plupload_button", function () {
				$("div.tagsdiv").each(function () {
					gmTagBox.flushTags(this, false, 1);
				});
			});
		}
	};
})(jQuery);
jQuery(document).ready(function () {
	gmTagBox.init();
});