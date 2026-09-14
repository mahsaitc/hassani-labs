/**
 * Quantivista front-end behaviour. Vanilla JS, no dependencies.
 */
(() => {
	'use strict';

	const d = document;
	const data = window.qvData || { ajaxUrl: '', nonce: '', i18n: {} };
	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	window.qvReady = true;

	/* ------------------------------------------------------------------
	 * Toast
	 * ---------------------------------------------------------------- */
	const toast = (message) => {
		const el = d.getElementById('qv-toast');
		if (!el || !message) return;
		el.textContent = message;
		el.hidden = false;
		requestAnimationFrame(() => el.classList.add('is-visible'));
		clearTimeout(el._timer);
		el._timer = setTimeout(() => {
			el.classList.remove('is-visible');
			setTimeout(() => { el.hidden = true; }, 300);
		}, 2600);
	};
	window.qvToast = toast;

	/* ------------------------------------------------------------------
	 * Sticky header state
	 * ---------------------------------------------------------------- */
	const header = d.getElementById('site-header');
	if (header) {
		const onScroll = () => header.classList.toggle('is-stuck', window.scrollY > 8);
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* ------------------------------------------------------------------
	 * Navigation: mobile drawer + disclosure submenus
	 * ---------------------------------------------------------------- */
	const navToggle = d.querySelector('[data-nav-toggle]');
	const nav = d.getElementById('primary-nav');

	const setNav = (open) => {
		if (!navToggle) return;
		navToggle.setAttribute('aria-expanded', String(open));
		d.body.classList.toggle('nav-open', open);
	};

	navToggle?.addEventListener('click', () => {
		setNav(navToggle.getAttribute('aria-expanded') !== 'true');
	});

	const closeSubmenus = (except) => {
		nav?.querySelectorAll('li.is-open').forEach((li) => {
			if (li === except) return;
			li.classList.remove('is-open');
			li.querySelector(':scope > .submenu-toggle')?.setAttribute('aria-expanded', 'false');
		});
	};

	nav?.addEventListener('click', (event) => {
		const btn = event.target.closest('.submenu-toggle');
		if (!btn) return;
		const li = btn.closest('li');
		const open = !li.classList.contains('is-open');
		closeSubmenus(li);
		li.classList.toggle('is-open', open);
		btn.setAttribute('aria-expanded', String(open));
	});

	d.addEventListener('click', (event) => {
		if (!event.target.closest('.primary-nav')) closeSubmenus();
	});

	// Leaving a desktop dropdown with the keyboard closes it.
	nav?.addEventListener('focusout', (event) => {
		const li = event.target.closest('.menu > li.is-open');
		if (li && !li.contains(event.relatedTarget)) {
			li.classList.remove('is-open');
			li.querySelector(':scope > .submenu-toggle')?.setAttribute('aria-expanded', 'false');
		}
	});

	/* ------------------------------------------------------------------
	 * Search panel
	 * ---------------------------------------------------------------- */
	const searchBtn = d.querySelector('[data-search-toggle]');
	const searchPanel = d.getElementById('search-panel');

	const setSearch = (open) => {
		if (!searchBtn || !searchPanel) return;
		searchPanel.hidden = !open;
		searchBtn.setAttribute('aria-expanded', String(open));
		if (open) searchPanel.querySelector('input[type="search"]')?.focus();
	};

	searchBtn?.addEventListener('click', () => setSearch(searchPanel.hidden));

	d.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') return;
		const openItem = nav?.querySelector('li.is-open');
		if (openItem) {
			closeSubmenus();
			openItem.querySelector(':scope > .submenu-toggle')?.focus();
			return;
		}
		if (searchPanel && !searchPanel.hidden) {
			setSearch(false);
			searchBtn.focus();
			return;
		}
		if (d.body.classList.contains('nav-open')) {
			setNav(false);
			navToggle.focus();
		}
		if (d.body.classList.contains('filters-open')) {
			setFilters(false);
		}
	});

	/* ------------------------------------------------------------------
	 * Hero slider
	 * ---------------------------------------------------------------- */
	const initSlider = (root) => {
		const slides = [...root.querySelectorAll('.hero__slide')];
		const dots = [...root.querySelectorAll('[data-slide-to]')];
		const pauseBtn = root.querySelector('[data-slider-pause]');
		const interval = parseInt(root.dataset.interval, 10) || 7000;
		let index = Math.max(0, slides.findIndex((s) => s.classList.contains('is-active')));
		let timer = null;
		let userPaused = reduceMotion;

		root.style.setProperty('--slide-duration', `${interval}ms`);

		const syncMedia = () => {
			slides.forEach((slide, i) => {
				slide.querySelectorAll('video').forEach((video) => {
					if (i === index && !reduceMotion) {
						const playing = video.play();
						if (playing && playing.catch) playing.catch(() => {});
					} else {
						video.pause();
					}
				});
			});
		};

		const go = (next) => {
			if (!slides.length) return;
			slides[index].classList.remove('is-active');
			slides[index].setAttribute('aria-hidden', 'true');
			slides[index].inert = true;
			dots[index]?.setAttribute('aria-current', 'false');

			index = (next + slides.length) % slides.length;

			slides[index].classList.add('is-active');
			slides[index].removeAttribute('aria-hidden');
			slides[index].inert = false;
			const dot = dots[index];
			if (dot) {
				dot.setAttribute('aria-current', 'true');
				// Restart the progress animation.
				dot.style.animation = 'none';
				void dot.offsetWidth; // eslint-disable-line no-void
				dot.style.animation = '';
			}
			syncMedia();
		};

		const stop = () => clearInterval(timer);
		const start = () => {
			stop();
			root.classList.toggle('is-paused', userPaused);
			if (!userPaused && slides.length > 1) timer = setInterval(() => go(index + 1), interval);
		};

		slides.forEach((slide, i) => {
			if (i !== index) {
				slide.setAttribute('aria-hidden', 'true');
				slide.inert = true;
			}
		});

		root.querySelector('[data-slider-prev]')?.addEventListener('click', () => { go(index - 1); start(); });
		root.querySelector('[data-slider-next]')?.addEventListener('click', () => { go(index + 1); start(); });
		dots.forEach((dot) => dot.addEventListener('click', () => { go(parseInt(dot.dataset.slideTo, 10)); start(); }));

		pauseBtn?.addEventListener('click', () => {
			userPaused = !userPaused;
			pauseBtn.setAttribute('aria-pressed', String(userPaused));
			pauseBtn.querySelector('[data-icon-pause]').hidden = userPaused;
			pauseBtn.querySelector('[data-icon-play]').hidden = !userPaused;
			if (userPaused) {
				root.querySelectorAll('video').forEach((v) => v.pause());
			} else {
				syncMedia();
			}
			start();
		});

		if (pauseBtn && userPaused) {
			pauseBtn.setAttribute('aria-pressed', 'true');
			pauseBtn.querySelector('[data-icon-pause]').hidden = true;
			pauseBtn.querySelector('[data-icon-play]').hidden = false;
		}

		// Pause while the user interacts, resume afterwards.
		root.addEventListener('mouseenter', stop);
		root.addEventListener('mouseleave', start);
		root.addEventListener('focusin', stop);
		root.addEventListener('focusout', (e) => { if (!root.contains(e.relatedTarget)) start(); });

		root.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowLeft') { go(index - 1); }
			if (e.key === 'ArrowRight') { go(index + 1); }
		});

		// Swipe.
		let startX = null;
		root.addEventListener('pointerdown', (e) => { startX = e.clientX; }, { passive: true });
		root.addEventListener('pointerup', (e) => {
			if (startX === null) return;
			const dx = e.clientX - startX;
			if (Math.abs(dx) > 50) { go(index + (dx < 0 ? 1 : -1)); start(); }
			startX = null;
		}, { passive: true });

		d.addEventListener('visibilitychange', () => (d.hidden ? stop() : start()));

		syncMedia();
		start();
	};

	d.querySelectorAll('[data-slider]').forEach(initSlider);

	/* ------------------------------------------------------------------
	 * Scroll-snap carousels
	 * ---------------------------------------------------------------- */
	d.querySelectorAll('[data-carousel]').forEach((root) => {
		const track = root.querySelector('[data-carousel-track]');
		const prev = root.querySelector('[data-carousel-prev]');
		const next = root.querySelector('[data-carousel-next]');
		if (!track) return;

		const update = () => {
			const max = track.scrollWidth - track.clientWidth - 2;
			if (prev) prev.disabled = track.scrollLeft <= 2;
			if (next) next.disabled = track.scrollLeft >= max;
		};

		const step = (dir) => {
			const item = track.firstElementChild;
			const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
			const amount = item ? item.getBoundingClientRect().width + gap : track.clientWidth;
			track.scrollBy({ left: dir * amount, behavior: reduceMotion ? 'auto' : 'smooth' });
		};

		prev?.addEventListener('click', () => step(-1));
		next?.addEventListener('click', () => step(1));
		track.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
		window.addEventListener('resize', update, { passive: true });
		update();
	});

	/* ------------------------------------------------------------------
	 * Scroll reveal + stat counters
	 * ---------------------------------------------------------------- */
	const revealEls = d.querySelectorAll('[data-reveal]');

	const countUp = (el) => {
		const raw = el.textContent.trim();
		const match = raw.match(/^([\d.,]+)(.*)$/);
		if (!match || reduceMotion) return;
		const target = parseFloat(match[1].replace(/,/g, ''));
		const decimals = (match[1].split('.')[1] || '').length;
		const suffix = match[2];
		const duration = 1400;
		const t0 = performance.now();
		const tick = (now) => {
			const p = Math.min(1, (now - t0) / duration);
			const eased = 1 - Math.pow(1 - p, 3);
			el.textContent = (target * eased).toFixed(decimals) + suffix;
			if (p < 1) requestAnimationFrame(tick);
			else el.textContent = raw;
		};
		requestAnimationFrame(tick);
	};

	if ('IntersectionObserver' in window && !reduceMotion) {
		// Stagger siblings that reveal together.
		revealEls.forEach((el) => {
			const siblings = el.parentElement ? [...el.parentElement.children].filter((c) => c.hasAttribute('data-reveal')) : [];
			const pos = siblings.indexOf(el);
			if (pos > 0) el.style.setProperty('--reveal-delay', `${Math.min(pos, 6) * 70}ms`);
		});

		const io = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				entry.target.classList.add('is-revealed');
				entry.target.querySelectorAll('[data-count]').forEach(countUp);
				io.unobserve(entry.target);
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

		revealEls.forEach((el) => io.observe(el));
	} else {
		revealEls.forEach((el) => el.classList.add('is-revealed'));
	}

	/* ------------------------------------------------------------------
	 * Copy link
	 * ---------------------------------------------------------------- */
	d.addEventListener('click', (event) => {
		const btn = event.target.closest('[data-copy-link]');
		if (!btn || !navigator.clipboard) return;
		navigator.clipboard.writeText(btn.dataset.copyLink).then(() => toast(data.i18n.copied));
	});

	/* ------------------------------------------------------------------
	 * Wishlist
	 * ---------------------------------------------------------------- */
	d.addEventListener('click', async (event) => {
		const btn = event.target.closest('[data-wishlist]');
		if (!btn) return;
		event.preventDefault();

		const id = btn.dataset.wishlist;
		btn.disabled = true;
		try {
			const body = new URLSearchParams({ action: 'qv_wishlist_toggle', nonce: data.nonce, product_id: id });
			const res = await fetch(data.ajaxUrl, { method: 'POST', credentials: 'same-origin', body });
			const json = await res.json();
			if (!json.success) throw new Error('wishlist');

			const on = !!json.data.in_wishlist;
			d.querySelectorAll(`[data-wishlist="${CSS.escape(id)}"]`).forEach((el) => {
				el.classList.toggle('is-active', on);
				el.setAttribute('aria-pressed', String(on));
			});
			d.querySelectorAll('[data-wishlist-count]').forEach((el) => { el.textContent = json.data.count; });
			toast(on ? data.i18n.added : data.i18n.removed);

			if (!on) {
				const row = btn.closest('[data-wishlist-item]');
				if (row) row.remove();
			}
		} catch (e) {
			toast('Something went wrong. Please try again.');
		} finally {
			btn.disabled = false;
		}
	});

	/* ------------------------------------------------------------------
	 * License tier selector (mirrors WooCommerce's variation <select>)
	 * ---------------------------------------------------------------- */
	d.querySelectorAll('[data-tier-group]').forEach((group) => {
		const select = d.getElementById(group.dataset.tierGroup);
		if (!select) return;

		group.addEventListener('change', (event) => {
			if (!event.target.matches('input[type="radio"]')) return;
			select.value = event.target.value;
			select.dispatchEvent(new Event('change', { bubbles: true }));
		});

		// Keep radios in sync when WooCommerce resets the form ("Clear").
		const syncFromSelect = () => {
			group.querySelectorAll('input[type="radio"]').forEach((radio) => {
				radio.checked = radio.value === select.value && select.value !== '';
			});
		};
		select.addEventListener('change', syncFromSelect);
		if (window.jQuery) window.jQuery(select).closest('form').on('reset_data', syncFromSelect);
		syncFromSelect();
	});

	/* ------------------------------------------------------------------
	 * Shop filter drawer (mobile)
	 * ---------------------------------------------------------------- */
	const filterToggle = d.querySelector('[data-filter-toggle]');
	const setFilters = (open) => {
		d.body.classList.toggle('filters-open', open);
		filterToggle?.setAttribute('aria-expanded', String(open));
		if (open) d.getElementById('shop-filters')?.focus();
	};
	filterToggle?.addEventListener('click', () => setFilters(!d.body.classList.contains('filters-open')));
	d.querySelectorAll('[data-filter-close]').forEach((btn) => btn.addEventListener('click', () => { setFilters(false); filterToggle?.focus(); }));

	// Auto-submit ordering / rating filters.
	d.querySelectorAll('[data-autosubmit]').forEach((el) => {
		el.addEventListener('change', () => el.form?.submit());
	});

	/* ------------------------------------------------------------------
	 * Cart count bump when WooCommerce refreshes fragments
	 * ---------------------------------------------------------------- */
	if (window.jQuery) {
		window.jQuery(d.body).on('added_to_cart', () => {
			d.querySelectorAll('.cart-count').forEach((el) => {
				el.classList.remove('is-bumped');
				void el.offsetWidth; // eslint-disable-line no-void
				el.classList.add('is-bumped');
			});
		});
	}
})();
