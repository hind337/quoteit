/*
|--------------------------------------------------------------------------
| Day / night switch
|--------------------------------------------------------------------------
| Clicking the button flips data-theme on <html> between "day" and "night".
| All the colors in app.css follow that attribute, and the choice is saved
| in the browser so it stays the same on the next visit.
*/
const themeButton = document.querySelector('[data-theme-toggle]');

themeButton?.addEventListener('click', () => {
    const root = document.documentElement;
    const nextTheme = root.dataset.theme === 'night' ? 'day' : 'night';

    root.dataset.theme = nextTheme;

    try {
        localStorage.setItem('quoteit-theme', nextTheme);
    } catch (error) {
        // Private browsing can block saving; the switch still works for this visit.
    }
});

/*
|--------------------------------------------------------------------------
| Sun on the log in / sign up pages
|--------------------------------------------------------------------------
| The sun reacts to what you do in the form:
|   • typing your name or email → it opens its eyes and greets you
|   • typing your password      → it shuts its eyes (no peeking!)
|   • pressing "Show"           → it peeks with one eye
|   • pointing at the button    → it looks ready
| Each mood is just a data-mood value; app.css shows the matching eyes and mouth.
*/
const authSuns = document.querySelectorAll('[data-auth-sun]');

if (authSuns.length > 0) {
    const sunLine = document.querySelector('[data-sun-line]');
    const passwordInput = document.querySelector('[data-password]');
    const showPasswordButton = document.querySelector('[data-show-password]');
    const readyButton = document.querySelector('[data-sun-ready]');

    const defaultLines = {
        asleep: '“Every line you keep is a little light.”',
        awake: '“Oh, hello!”',
        shut: '“I’m not looking, I promise.”',
        peek: '“Okay… maybe just a little peek.”',
    };

    const setMood = (mood, line) => {
        authSuns.forEach((sun) => {
            sun.dataset.mood = mood;
        });

        if (sunLine) {
            sunLine.textContent = line ?? defaultLines[mood];
        }
    };

    const passwordMood = () => (passwordInput.type === 'text' ? 'peek' : 'shut');

    // Name and email fields: open eyes, say this field's own line
    document.querySelectorAll('[data-sun-watch]').forEach((input) => {
        input.addEventListener('focus', () => setMood('awake', input.dataset.sunSay));
        input.addEventListener('blur', () => setMood('asleep'));
    });

    // Password field: shut eyes (or peek if the password is visible)
    passwordInput?.addEventListener('focus', () => setMood(passwordMood()));
    passwordInput?.addEventListener('blur', () => setMood('asleep'));

    // "Show" / "Hide" button
    showPasswordButton?.addEventListener('mousedown', (event) => event.preventDefault()); // keep focus in the field
    showPasswordButton?.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';

        passwordInput.type = isHidden ? 'text' : 'password';
        showPasswordButton.textContent = isHidden ? 'Hide' : 'Show';
        passwordInput.focus();
        setMood(passwordMood());
    });

    // Submit button: look ready, unless the visitor is typing somewhere
    readyButton?.addEventListener('mouseenter', () => {
        if (document.activeElement.tagName !== 'INPUT') {
            setMood('awake', '“Ready when you are.”');
        }
    });
    readyButton?.addEventListener('mouseleave', () => {
        if (document.activeElement.tagName !== 'INPUT') {
            setMood('asleep');
        }
    });
}

/*
|--------------------------------------------------------------------------
| Home page: topic filter on the wall
|--------------------------------------------------------------------------
| Each card has data-topic="Work", "Hope"… and data-mine="1" if it's yours.
| Clicking a topic button hides every card from other topics.
*/
const topicButtons = document.querySelectorAll('[data-topic-filter]');

topicButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const chosenTopic = button.dataset.topicFilter;

        topicButtons.forEach((otherButton) => {
            otherButton.setAttribute('aria-pressed', otherButton === button ? 'true' : 'false');
        });

        document.querySelectorAll('[data-topic]').forEach((card) => {
            const matches =
                chosenTopic === 'All' ||
                (chosenTopic === 'Mine' ? card.dataset.mine === '1' : card.dataset.topic === chosenTopic);

            card.hidden = !matches;
        });
    });
});

/*
|--------------------------------------------------------------------------
| Home page: the sun's path and the shadows
|--------------------------------------------------------------------------
| Every minute we look at the visitor's clock and:
|   1. move the sun dot along the dotted arc (sunrise on the left, sunset on the right)
|   2. turn the quote cards' shadows away from the sun — long in the morning
|      and evening, short at noon, like real shadows
| Sunrise and sunset are fixed (06:00 and 17:30) to keep things simple.
|
| Demo trick: add ?hour=10 (any hour 0–23.5) to the address to pretend it's
| that time — handy for showing the sun moving when presenting at night.
*/
const hourFromAddress = new URLSearchParams(window.location.search).get('hour');
const sunDots = document.querySelectorAll('[data-sun-dot]');

if (sunDots.length > 0) {
    const SUNRISE_HOUR = 6;
    const SUNSET_HOUR = 17.5;

    const placeTheSun = () => {
        const now = new Date();
        const hour = hourFromAddress !== null ? Number(hourFromAddress) : now.getHours() + now.getMinutes() / 60;
        const isSunUp = hour >= SUNRISE_HOUR && hour <= SUNSET_HOUR;

        // 0 at sunrise → 1 at sunset, turned into an angle on the arc (left = 180°, right = 0°)
        const progress = Math.min(1, Math.max(0, (hour - SUNRISE_HOUR) / (SUNSET_HOUR - SUNRISE_HOUR)));
        const angle = Math.PI - progress * Math.PI;

        // The arc in the SVG is centered at (640, 400), 440 wide and 273 tall
        const x = 640 + Math.cos(angle) * 440;
        const y = isSunUp ? 400 - Math.sin(angle) * 273 : 440;

        sunDots.forEach((dot) => {
            dot.setAttribute('cx', x.toFixed(1));
            dot.setAttribute('cy', y.toFixed(1));
        });

        document.documentElement.dataset.sun = isSunUp ? 'up' : 'down';

        // Shadow: points away from the sun, longer when the sun is low
        const sunHeight = Math.sin(angle);
        const length = 6 + 26 * (1 - sunHeight);
        const offsetX = (-Math.cos(angle) * length).toFixed(1);
        const offsetY = (8 + 6 * (1 - sunHeight)).toFixed(1);
        const blur = (14 + 18 * (1 - sunHeight)).toFixed(0);

        document.documentElement.style.setProperty(
            '--sun-shadow',
            isSunUp ? `${offsetX}px ${offsetY}px ${blur}px rgb(70 40 0 / 0.22)` : '0 8px 20px rgb(70 40 0 / 0.18)',
        );
    };

    placeTheSun();
    setInterval(placeTheSun, 60 * 1000);
}

/*
|--------------------------------------------------------------------------
| Warmth: liking a quote
|--------------------------------------------------------------------------
| Clicking the little sun likes (or unlikes) a quote:
|   • the card pulses and three small rays fly out straight away
|   • the like is sent to the server (LikeController), which saves it
|   • the server answers with the new count and warmth, and the card updates
*/
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const sendRays = (card) => {
    const directions = [
        ['20px', '-90px'],
        ['40px', '-120px'],
        ['-10px', '-70px'],
    ];

    directions.forEach(([flyX, flyY], index) => {
        const ray = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        ray.setAttribute('viewBox', '0 0 20 20');
        ray.setAttribute('aria-hidden', 'true');
        ray.classList.add('like-ray');
        ray.style.setProperty('--fly-x', flyX);
        ray.style.setProperty('--fly-y', flyY);
        ray.style.animationDelay = `${index * 0.08}s`;
        ray.innerHTML = '<path fill="#e8a80d" d="M10 0c1 6 4 9 10 10-6 1-9 4-10 10-1-6-4-9-10-10 6-1 9-4 10-10Z" />';

        card.append(ray);
        setTimeout(() => ray.remove(), 1000);
    });
};

const showLike = (button, card, { liked, likes, warmth }) => {
    button.setAttribute('aria-pressed', liked ? 'true' : 'false');
    button.setAttribute('aria-label', liked ? 'Remove like' : 'Like this quote');
    button.querySelector('[data-like-count]').textContent = likes;
    card.dataset.warmth = warmth;
};

document.querySelectorAll('[data-like-button]').forEach((button) => {
    button.addEventListener('click', async () => {
        const card = button.closest('.quote-card');
        const isLiking = button.getAttribute('aria-pressed') !== 'true';

        if (isLiking) {
            sendRays(card);
            card.classList.remove('card-pulse');
            void card.offsetWidth; // restart the pulse animation
            card.classList.add('card-pulse');
        }

        button.disabled = true;

        try {
            const response = await fetch(button.dataset.likeUrl, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });

            if (!response.ok) {
                throw new Error(`The like was not saved (${response.status})`);
            }

            showLike(button, card, await response.json());
        } catch (error) {
            console.error(error);
        } finally {
            button.disabled = false;
        }
    });
});

/*
|--------------------------------------------------------------------------
| Share pop-up: reopen it when the server sent back a mistake
|--------------------------------------------------------------------------
*/
document.querySelector('[popover][data-open-now]')?.showPopover();

/*
|--------------------------------------------------------------------------
| Reposting a quote
|--------------------------------------------------------------------------
| Clicking the two arrows reposts (or un-reposts) a quote. The server saves it
| (RepostController) and answers with the new count. After a reload the
| reposted quote moves to the top of the wall with "… reposted" above it.
*/
document.querySelectorAll('[data-repost-button]').forEach((button) => {
    button.addEventListener('click', async () => {
        button.disabled = true;

        try {
            const response = await fetch(button.dataset.repostUrl, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });

            if (!response.ok) {
                throw new Error(`The repost was not saved (${response.status})`);
            }

            const { reposted, reposts } = await response.json();

            button.setAttribute('aria-pressed', reposted ? 'true' : 'false');
            button.setAttribute('aria-label', reposted ? 'Undo repost' : 'Repost this quote');
            button.querySelector('[data-repost-count]').textContent = reposts;
        } catch (error) {
            console.error(error);
        } finally {
            button.disabled = false;
        }
    });
});

/*
|--------------------------------------------------------------------------
| Phone wallpaper maker
|--------------------------------------------------------------------------
| Draws the quote on a <canvas> the size of a phone screen (1170 × 2532),
| using the colour, frame and font picked in the form. "Download image"
| saves the canvas as a PNG file.
*/
const wallpaperMaker = document.querySelector('[data-wallpaper-maker]');

if (wallpaperMaker) {
    const canvas = wallpaperMaker.querySelector('[data-wallpaper-canvas]');
    const options = wallpaperMaker.querySelector('[data-wallpaper-options]');
    const context = canvas.getContext('2d');
    const { text, author, source } = wallpaperMaker.dataset;
    const WIDTH = canvas.width;
    const HEIGHT = canvas.height;

    // Split the quote into lines that fit the given width
    const wrapLines = (words, maxWidth) => {
        const lines = [];
        let line = '';

        words.split(' ').forEach((word) => {
            const tryLine = line ? `${line} ${word}` : word;

            if (context.measureText(tryLine).width > maxWidth && line) {
                lines.push(line);
                line = word;
            } else {
                line = tryLine;
            }
        });

        return [...lines, line];
    };

    const roundedRect = (x, y, width, height, radius) => {
        context.beginPath();
        context.roundRect(x, y, width, height, radius);
    };

    const draw = () => {
        const chosen = new FormData(options);
        const [background, ink, accent] = JSON.parse(options.querySelector('[name="background"]:checked').dataset.colors);
        const frame = chosen.get('frame');
        const isSerif = chosen.get('font') === 'Serif';
        const showSource = chosen.has('show_source') && source;

        // Background
        context.fillStyle = background;
        context.fillRect(0, 0, WIDTH, HEIGHT);

        // Quote text: the size shrinks a little for long quotes
        const fontSize = text.length > 110 ? 96 : text.length > 60 ? 116 : 136;
        context.font = isSerif
            ? `italic 500 ${fontSize}px "Cormorant Garamond", Georgia, serif`
            : `600 ${fontSize * 0.82}px "Instrument Sans", system-ui, sans-serif`;
        const lines = wrapLines(`“${text}”`, WIDTH - 300);
        const lineHeight = fontSize * 1.25;

        // The block sits a little below the middle, leaving room for the lock-screen clock
        const blockHeight = lines.length * lineHeight + (showSource ? 110 : 60);
        const top = HEIGHT * 0.55 - blockHeight / 2;

        // Frame around the text block
        const frameBox = { x: 90, y: top - 130, width: WIDTH - 180, height: blockHeight + 250 };
        context.strokeStyle = accent;

        if (frame === 'Line') {
            context.lineWidth = 6;
            roundedRect(frameBox.x, frameBox.y, frameBox.width, frameBox.height, 18);
            context.stroke();
        } else if (frame === 'Double') {
            context.lineWidth = 5;
            roundedRect(frameBox.x, frameBox.y, frameBox.width, frameBox.height, 6);
            context.stroke();
            roundedRect(frameBox.x + 22, frameBox.y + 22, frameBox.width - 44, frameBox.height - 44, 4);
            context.stroke();
        } else if (frame === 'Card') {
            context.fillStyle = `${ink}14`; // the text colour, very see-through
            roundedRect(frameBox.x, frameBox.y, frameBox.width, frameBox.height, 70);
            context.fill();
        }

        // The quote
        context.fillStyle = ink;
        context.textAlign = 'center';
        context.textBaseline = 'top';
        lines.forEach((line, index) => context.fillText(line, WIDTH / 2, top + index * lineHeight));

        // Author and source
        let y = top + lines.length * lineHeight + 30;
        context.font = '500 48px "Instrument Sans", system-ui, sans-serif';
        context.globalAlpha = 0.8;
        context.fillText(`— ${author}`, WIDTH / 2, y);

        if (showSource) {
            y += 66;
            context.font = '42px "Instrument Sans", system-ui, sans-serif';
            context.fillText(source, WIDTH / 2, y);
        }

        // Small signature at the bottom
        context.globalAlpha = 0.45;
        context.font = 'italic 700 46px "Cormorant Garamond", Georgia, serif';
        context.fillText('QuoteIt', WIDTH / 2, HEIGHT - 190);
        context.globalAlpha = 1;
    };

    options.addEventListener('change', draw);

    wallpaperMaker.querySelector('[data-wallpaper-download]').addEventListener('click', () => {
        canvas.toBlob((blob) => {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'quoteit-wallpaper.png';
            link.click();
            URL.revokeObjectURL(link.href);
        });
    });

    draw();
    // Draw again once the fonts have loaded, so the picture uses them
    document.fonts.ready.then(draw);
}
