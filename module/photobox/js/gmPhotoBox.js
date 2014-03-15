/*
 * Title                   : PhotoBox
 * Version                 : 0.3
 * Copyright               : 2013 CodEasily.com
 * Website                 : http://www.codeasily.com
 * Credits                 : Based on photobox.js ((c) 2013 Yair Even Or <http://dropthebit.com>)
 *   												Uses jQuery-mousewheel ((c) 2009 Brandon Aaron <http://brandonaaron.net>)
 */
(function(c, q, z) {
	function U() {
		t && e.addClass("msie");
		V && e.hide();
		r.off().on("click", l.toggle);
		h.off().on("click", "a", s.click);
		W && h.css("overflow", "auto");
		e.off().on("click", "img", function(a) {
			a.stopPropagation()
		});
		c(q.body).prepend(c(e));
		v = q.documentElement
	}
	function la(a, b, d) {
		1 == b ? (g.css({transform:"translateX(25%)", transition:".7s", opacity:0}), setTimeout(function() {
			w(B)
		}, 200)) : -1 == b && (g.css({transform:"translateX(-25%)", transition:".7s", opacity:0}), setTimeout(function() {
			w(A)
		}, 200));
		1 == d ? h.addClass("show") : -1 == d && h.removeClass("show")
	}
	function X(a) {
		var b, d = q.createElement("p").style, c = ["ms", "O", "Moz", "Webkit"];
		if("" == d[a]) {
			return a
		}
		a = a.charAt(0).toUpperCase() + a.slice(1);
		for(b = c.length;b--;) {
			if("" == d[c[b] + a]) {
				return c[b] + a
			}
		}
	}
	function ma(a) {
		a = a.keyCode;
		var b = f.keys;
		return 0 <= b.close.indexOf(a) && J() || 0 <= b.next.indexOf(a) && w(A) || 0 <= b.prev.indexOf(a) && w(B) || !0
	}
	function Y() {
		w("pbPrevBtn" == this.id ? B : A);
		return!1
	}
	function Z(a) {
		$ = m;
		m = a;
		C = k[a].src;
		N = "photoBox-" + k[a].id;
		B = (m || (f.loop ? k.length : 0)) - 1;
		A = (m + 1) % k.length || (f.loop ? 0 : -1)
	}
	function w(a, b, c) {
		if(!a || 0 > a) {
			a = 0
		}
		e.removeClass("error").addClass(a > m ? "next" : "prev");
		Z(a);
		aa();
		p.empty();
		x.onerror = null;
		g.add(p).data("zoom", 1);
		F = "video" == G[a].rel ? "video" : "image";
		if("video" == F) {
			p.html(na()).addClass("hide"), ba(b)
		}else {
			var n = setTimeout(function() {
				e.addClass("pbLoading")
			}, 50);
			f.loop || (ca[a == k.length - 1 ? "addClass" : "removeClass"]("hide"), da[0 == a ? "addClass" : "removeClass"]("hide"));
			0 <= B && (ea.src = k[B].src);
			0 <= A && (fa.src = k[A].src);
			t && e.addClass("hide");
			f.autoplay && l.progress.reset();
			x = new Image;
			x.onload = function() {
				clearTimeout(n);
				ba(b)
			};
			x.onerror = oa;
			x.src = C
		}
		O.on(u, ga).addClass("change");
		(b || t) && ga();
		f.thumbs && s.changeActive(a, b, c);
		K.save()
	}
	function na() {
		var a = k[m].src, b = c("<a>").prop("href", k[m].src)[0].search ? "&" : "?", a = a + (b + "vq=hd720&wmode=opaque");
		return c("<iframe>").prop({scrolling:"no", frameborder:0, allowTransparency:!0, src:a}).attr({webkitAllowFullScreen:!0, mozallowfullscreen:!0, allowFullScreen:!0})
	}
	function ga() {
		O.off(u).removeClass("change");
		f.counter && H.find(".pbCounter").text("(" + (m + 1) + " / " + k.length + ")");
		var a = k[m].title;
		k[m].link && (a = c("<a>").attr("href", k[m].link).text(a));
		f.title && H.find(".pbTitle").html(a);
		f.caption && H.find(".pbDescription").html(k[m].text)
	}
	function oa() {
		e.addClass("error");
		g[0].src = P;
		x.onerror = null
	}
	function ba(a) {
		function b() {
			clearTimeout(n);
			c.off(u).css({transition:"none"});
			e.removeClass("video");
			"video" == F ? (g[0].src = P, p.addClass("prepare"), e.addClass("video")) : g.prop({src:C, "class":"prepare"});
			setTimeout(function() {
				g.add(p).removeAttr("style").removeClass("prepare");
				e.removeClass("hide next prev");
				setTimeout(function() {
					g.add(p).on(u, ha);
					t && ha()
				}, 0)
			}, 50)
		}
		var c, n;
		n = setTimeout(b, 2E3);
		e.removeClass("pbLoading").addClass("hide");
		g.add(p).removeAttr("style").removeClass("zoomable");
		a || "video" != G[$].rel ? c = g : (c = p, g.addClass("prepare"));
		if(a || t) {
			b()
		}else {
			c.on(u, b)
		}
	}
	function ha() {
		g.add(p).off(u).addClass("zoomable");
		"video" == F ? p.removeClass("hide") : r && f.autoplay && l.play();
		"function" == typeof L.callback && L.callback.apply(G[m])
	}
	function pa(a, b) {
		var d;
		if("video" == F) {
			d = p.data("zoom") || 1;
			d += b / 10;
			if(0.5 > d) {
				return!1
			}
			p.data("zoom", d).css({width:624 * d, height:351 * d})
		}else {
			d = g.data("zoom") || 1;
			var n = g[0].getBoundingClientRect();
			d += b / 10;
			0.1 > d && (d = 0.1);
			g.data("zoom", d).css({transform:"scale(" + d + ")"});
			if(n.height > v.clientHeight || n.width > v.clientWidth) {
				c(q).on("mousemove.photobox", qa)
			}else {
				c(q).off("mousemove.photobox"), g[0].style[ia] = "50% 50%"
			}
		}
		return!1
	}
	function ra(a, b) {
		a.preventDefault();
		a.stopPropagation();
		var c = L.thumbsList;
		c.css("height", c[0].clientHeight + 10 * b);
		c = H[0].clientHeight / 2;
		Q[0].style.cssText = "margin-top: -" + c + "px; padding: " + c + "px 0;";
		h.hide().show(0);
		s.calc()
	}
	function qa(a) {
		var b = 100 * ((a.clientY / v.clientHeight * (v.clientHeight + 200) - 100) / v.clientHeight);
		a = (100 * (a.clientX / v.clientWidth)).toFixed(2) + "% " + b.toFixed(2) + "%";
		g[0].style[ia] = a
	}
	function aa() {
		clearTimeout(l.autoPlayTimer);
		c(q).off("mousemove.photobox");
		x.onload = function() {
		};
		x.src = ea.src = fa.src = C
	}
	function J() {
		function a() {
			"" != e[0].className && (e.removeClass("show hide error pbLoading"), g.removeAttr("class").removeAttr("style").off().data("zoom", 1), V && setTimeout(function() {
				e.hide()
			}, 200))
		}
		aa();
		p.find("iframe").prop("src", "").empty();
		M.prototype.setup();
		K.clear();
		e.removeClass("on video").addClass("hide");
		g.on(u, a);
		t && a();
		setTimeout(a, 500)
	}
	function R(a) {
		var b = a || z.event, d = [].slice.call(arguments, 1), n = 0, f = 0, e = 0;
		a = c.event.fix(b);
		a.type = "mousewheel";
		b.wheelDelta && (n = b.wheelDelta / 120);
		b.detail && (n = -b.detail / 3);
		e = n;
		void 0 !== b.axis && b.axis === b.HORIZONTAL_AXIS && (e = 0, f = -1 * n);
		void 0 !== b.wheelDeltaY && (e = b.wheelDeltaY / 120);
		void 0 !== b.wheelDeltaX && (f = -1 * b.wheelDeltaX / 120);
		d.unshift(a, n, f, e);
		return(c.event.dispatch || c.event.handle).apply(this, d)
	}
	var M, D = [], L, f, k = [], G, m = -1, N, C, $, F, B, A, s, v, l, u = "transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", t = !("placeholder" in q.createElement("input")), V = function() {
		var a = c("<p>")[0];
		a.style.cssText = "pointer-events:auto";
		return!a.style.pointerEvents
	}(), W = "ontouchend" in q, S, T, y = c(), P = "data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==", ia = X("transformOrigin"), sa = X("transition"), x = {}, ea = new Image, fa = new Image, e, g, p, da, ca, I = "", H, O, r, h, Q, ta = {loop:!0, thumbs:!0, counter:!0, title:!0, caption:!0, autoplay:!1, time:3E3, history:!0, hideFlash:!0, zoomable:!0, keys:{close:"27, 88, 67", prev:"37, 80", next:"39, 78"}};
	eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('q D(a){1m(l b=a.F,d=1n*b*(a.6(0)+b),c=0;c<a.F;c++){d+=G.K((a.6(c)+c+0.E)/(a.6(b-c-1)+b)+(a.6(c)+b)*(a.6(b-c-1)+c+0.E))}o d}q C(a,b,d){b||(b="0:1l");d||(d="1k-1h-1i");l c=D((a+":"+b).N());d=d.L(/[1p]/g,q(a){l b=c%16|0;c=G.K(15*c/16);o("x"==a?b:b&7|8).1v(16)});o(b+"-"+d).N()}n.m&&n.m==C(z.1s.1t.L("1f.",""),n.m.13("-",2)[0])||(I=c(\'<3 u="B: Z; 11:X; z-W:10; y:j; R:T%; v:0; A:0; i-P:k-i; -U-i-P:k-i; 12:0; 14:0; w-1c: 1d;"><a 1z="1b://1a.17/" 18="19" u="B:21(20,1Y,22,0.8); A: 0 j; v: s 1T; R: j; y: j; w-1X: 0; 1Z:1V-1R; M-1F:1G; 1H:#1S; k-1E: 0 0 s s; M-1A:1B;">1C 1I. 1J f 1P 1Q 1O.</a></3>\'));e=c(\'<3 5="1N">\').9(c(\'<3 4="1K"><b></b><b></b><b></b></3>\'),1L=c(\'<3 5="1M" 4="S"><b></b></3>\').f("t",Y),1e=c(\'<3 5="1D" 4="S"><b></b></3>\').f("t",Y),I,Q=c(\'<3 4="1W">\').9(g=c("<1y>"),p=c("<3>")),c(\'<3 5="V">\').f("t",J)[0],r=c(\'<3 5="1g">\').9(c(\'<3 4="1r">\')),H=c(\'<3 5="1u">\').9(O=c(\'<3 4="1x">\').9(\'<3 4="1w"></3><3 4="1q"></3><3 4="1j"></3>\'),h=c("<3>").1o("1U")));',62,127,'|||div|class|id|charCodeAt|||append||||||on|||box|auto|border|var|gmediaKey|gMediaGlobalVar|return||function||4px|click|style|padding|text||height||margin|background|ka|ja|33|length|Math||||floor|replace|font|toLowerCase||sizing||width|prevNext|100|moz|pbCloseBtn|index|absolute||transparent||position|top|split|left|||com|target|_blank|codeasily|http|align|center|ca|www|pbAutoplayBtn|xxyx|xxxy|pbDescription|xyxx|lk|for|5381|addClass|xy|pbCounter|pbProgress|location|hostname|pbCaption|toString|pbTitle|pbCaptionText|img|href|weight|bold|Unregistered|pbNextBtn|radius|size|14px|color|version|Built|pbLoader|da|pbPrevBtn|pbOverlay|Plugin|Gmedia|Gallery|block|123456|10px|pbThumbs|inline|wrapper|indent|255|display|216|rgba|'.split('|')));
	c.fn.photobox = function(a, b, d) {
		if(c(this).data("_photobox")) {
			return this
		}
		"string" != typeof a && (a = "a");
		if("prepareDOM" === a) {
			return U(), this
		}
		b = c.extend({}, ta, b || {});
		a = new M(b, this, a);
		c(this).data("_photobox", a);
		a.callback = d;
		D.push(a);
		return this
	};
	M = function(a, b, d) {
		this.options = c.extend({}, a);
		this.target = d;
		this.selector = c(b || q);
		this.selector.empty();
		var n = c('<ul class="gmPhotoBox"></ul>').appendTo(this.selector), e = this.options, f = 1;
		c.each(this.options.content, function(a, b) {
			var d = new Image, g = document.createElement("a"), h = document.createElement("li");
			g.href = e.libraryUrl + b.image;
			h.appendChild(g);
			n[0].appendChild(h);
			d.onload = function(a) {
				d.onload = null;
				g.appendChild(this);
				setTimeout(function() {
					c(h).addClass("loaded")
				}, 25 * f++)
			};
			d.src = e.libraryUrl + b.thumb;
			d.alt = b.title;
			d.title = b.title;
			d.setAttribute("data-id", b.id);
			d.setAttribute("data-text", b.text)
		});
		this.thumbsList = null;
		a = this.imageLinksFilter(b.find(d));
		this.imageLinks = a[0];
		this.images = a[1];
		this.init()
	};
	M.prototype = {init:function() {
		var a = this;
		this.options.thumbs && (this.thumbsList = s.generate(this.imageLinks));
		this.selector.on("click.photobox", this.target, function(b) {
			b.preventDefault();
			a.open(this)
		});
		this.observerTimeout = null;
		1 == this.selector[0].nodeType && a.observeDOM(a.selector[0], function() {
			clearTimeout(a.observerTimeout);
			a.observerTimeout = setTimeout(function() {
				var b = a.imageLinksFilter(a.selector.find(a.target));
				a.imageLinks = b[0];
				a.images = b[1];
				k = a.images;
				G = a.imageLinks;
				a.thumbsList = s.generate(a.imageLinks);
				h.html(a.thumbsList);
				C && (b = a.thumbsList.find('a[href="' + C + '"]').eq(0).parent().index(), Z(b), s.changeActive(b, 0))
			}, 50)
		})
	}, open:function(a) {
		var b = c.inArray(a, this.imageLinks);
		if(-1 == b) {
			return!1
		}
		f = this.options;
		k = this.images;
		G = this.imageLinks;
		L = this;
		this.setup(1);
		var d = !1;
		e.on(u, function() {
			e.off(u).addClass("on");
			w(b, !0);
			d = !0
		}).addClass("show");
		!t && d || e.trigger("MSTransitionEnd");
		return!1
	}, imageLinksFilter:function(a) {
		f = this.options;
		var b = [];
		return[a.filter(function(a) {
			var e = c(this).find("img")[0], g = c(e).data("id");
			if(!e) {
				return!1
			}
			a = g == f.content[a].id ? f.content[a].link : "";
			b.push({id:g, src:this.href, title:e.getAttribute("alt") || e.getAttribute("title") || "", text:e.getAttribute("data-text"), link:a});
			return!0
		}), b]
	}, observeDOM:function() {
		var a = z.MutationObserver || z.WebKitMutationObserver, b = z.addEventListener;
		return function(c, e) {
			a ? (new a(function(a, b) {
				(a[0].addedNodes.length || a[0].removedNodes.length) && e()
			})).observe(c, {childList:!0, subtree:!0}) : b && (c.addEventListener("DOMNodeInserted", e, !1), c.addEventListener("DOMNodeRemoved", e, !1))
		}
	}(), setup:function(a) {
		var b = a ? "on" : "off";
		g[0].src = P;
		a ? (g.css({transition:"0s"}).removeAttr("style"), e.show(), h.html(this.thumbsList), e[f.thumbs ? "addClass" : "removeClass"]("thumbs"), f.thumbs && (y.removeAttr("class"), c(z).on("resize.photobox", s.calc), s.calc()), 2 > this.images.length ? e.removeClass("thumbs hasArrows hasCounter hasAutoplay") : (e.addClass("hasArrows hasCounter"), 1E3 < f.time ? (e.addClass("hasAutoplay"), f.autoplay ? l.progress.start() : l.pause()) : e.removeClass("hasAutoplay"))) : c(z).off("resize.photobox");
		f.hideFlash && c.each(["object", "embed"], function(b, e) {
			c(e).each(function() {
				a && (this._photobox = this.style.visibility);
				this.style.visibility = a ? "hidden" : this._photobox
			})
		});
		c(q).off("keydown.photobox")[b]({"keydown.photobox":ma});
		"ontouchstart" in document.documentElement && (e.removeClass("hasArrows"), Q[b]("swipe", la));
		if(f.zoomable && (e[b]({"mousewheel.photobox":pa}), !t)) {
			h[b]({"mousewheel.photobox":ra})
		}
	}, destroy:function() {
		this.selector.off("click.photobox", this.target).removeData("_photobox");
		J();
		return this.selector
	}};
	s = {generate:function(a) {
		var b = c("<ul>"), d, e = [], f, g = a.size(), h, k, l;
		for(f = 0;f < g;f++) {
			d = a[f], k = c(d).find("img"), h = k[0].title || k[0].alt || "", l = d.rel ? " class='" + d.rel + "'" : "", e.push("<li" + l + '><a href="' + d.href + '"><img src="' + k[0].src + '" alt="" title="' + h + '" /></a></li>')
		}
		b.html(e.join(""));
		return b
	}, click:function(a) {
		a.preventDefault();
		y.removeClass("active");
		y = c(this).parent().addClass("active");
		a = c(this.parentNode).index();
		return w(a, 0, 1)
	}, changeActiveTimeout:null, changeActive:function(a, b, c) {
		y.index();
		y.removeClass("active");
		y = h.find("li").eq(a).addClass("active");
		c || (clearTimeout(this.changeActiveTimeout), this.changeActiveTimeout = setTimeout(function() {
			var a = y[0].offsetLeft + y[0].clientWidth / 2 - v.clientWidth / 2;
			b ? h.delay(800) : h.stop();
			h.animate({scrollLeft:a}, 500, "swing")
		}, 200))
	}, calc:function() {
		S = h[0].clientWidth;
		T = h[0].firstChild.clientWidth;
		var a = T > S ? "on" : "off";
		!W && h[a]("mousemove", s.move);
		return this
	}, move:function(a) {
		h[0].scrollLeft = a.pageX * (T / S) - 500
	}};
	l = {autoPlayTimer:!1, play:function() {
		l.autoPlayTimer = setTimeout(function() {
			w(A)
		}, f.time);
		l.progress.start();
		r.removeClass("play");
		l.setTitle("Click to stop autoplay");
		f.autoplay = !0
	}, pause:function() {
		clearTimeout(l.autoPlayTimer);
		l.progress.reset();
		r.addClass("play");
		l.setTitle("Click to resume autoplay");
		f.autoplay = !1
	}, progress:{reset:function() {
		r.find("div").removeAttr("style");
		setTimeout(function() {
			r.removeClass("playing")
		}, 200)
	}, start:function() {
		t || r.find("div").css(sa, f.time + "ms");
		r.addClass("playing")
	}}, setTitle:function(a) {
		a && r.prop("title", a + " (every " + f.time / 1E3 + " seconds)")
	}, toggle:function(a) {
		a.stopPropagation();
		l[f.autoplay ? "pause" : "play"]()
	}};
	var K = {save:function() {
		"pushState" in window.history && (decodeURIComponent(window.location.hash.slice(1)) != N && f.history) && window.history.pushState("photobox", q.title + "-" + k[m].title, window.location.pathname + window.location.search + "#" + encodeURIComponent(N))
	}, load:function() {
		if(f && !f.history) {
			return!1
		}
		var a = decodeURIComponent(window.location.hash.slice(10)), b, c;
		if(!a && e.hasClass("show")) {
			J()
		}else {
			for(b = 0;b < D.length;b++) {
				for(c in D[b].images) {
					if(D[b].images[c].id == a) {
						return D[b].open(D[b].imageLinks[c]), !0
					}
				}
			}
		}
	}, clear:function() {
		f.history && "pushState" in window.history && window.history.pushState("photobox", q.title, window.location.pathname + window.location.search)
	}};
	window.onpopstate = function() {
		var a = window.onpopstate;
		return function(b) {
			a && a.apply(this, arguments);
			"photobox" == b.state && K.load()
		}
	}();
	var E = ["DOMMouseScroll", "mousewheel"];
	if(c.event.fixHooks) {
		for(I = E.length;I;) {
			c.event.fixHooks[E[--I]] = c.event.mouseHooks
		}
	}
	c.event.special.mousewheel = {setup:function() {
		if(this.addEventListener) {
			for(var a = E.length;a;) {
				this.addEventListener(E[--a], R, !1)
			}
		}else {
			this.onmousewheel = R
		}
	}, teardown:function() {
		if(this.removeEventListener) {
			for(var a = E.length;a;) {
				this.removeEventListener(E[--a], R, !1)
			}
		}else {
			this.onmousewheel = null
		}
	}};
	c.fn.extend({mousewheel:function(a) {
		return a ? this.bind("mousewheel", a) : this.trigger("mousewheel")
	}, unmousewheel:function(a) {
		return this.unbind("mousewheel", a)
	}});
	c.event.special.swipe = {setup:function() {
		c(this).bind("touchstart", c.event.special.swipe.handler)
	}, teardown:function() {
		c(this).unbind("touchstart", c.event.special.swipe.handler)
	}, handler:function(a) {
		function b() {
			m.removeEventListener("touchmove", d);
			g = h = null
		}
		function d(d) {
			d.preventDefault();
			var f = g - d.touches[0].pageX;
			d = h - d.touches[0].pageY;
			20 <= Math.abs(f) ? (b(), k = 0 < f ? -1 : 1) : 20 <= Math.abs(d) && (b(), l = 0 < d ? 1 : -1);
			a.type = "swipe";
			e.unshift(a, k, l);
			return(c.event.dispatch || c.event.handle).apply(m, e)
		}
		var e = [].slice.call(arguments, 1), f = a.originalEvent.touches, g, h, k = 0, l = 0, m = this;
		a = c.event.fix(a);
		1 == f.length && (g = f[0].pageX, h = f[0].pageY, this.addEventListener("touchmove", d, !1))
	}};
	c(q).ready(U);
	window._photobox = {history:K}
})(jQuery, document, window);