const body = document.body;
const main = document.getElementById("Main");

// Current (dx/dy) and target (sx/sy) scroll positions
let sx = 0, sy = 0;
let dx = 0, dy = 0;

// Fix the main element
main.style.position = 'fixed';
main.style.top = '0';
main.style.left = '0';
main.style.width = '100%';
main.style.transformStyle = 'preserve-3d';
main.style.backfaceVisibility = 'hidden';

// Function to update body height dynamically
function updateBodyHeight() {
    body.style.height = main.scrollHeight + 'px';
}
updateBodyHeight();
window.addEventListener('resize', updateBodyHeight);

// Update target scroll on normal window scroll
window.addEventListener('scroll', () => {
    sx = window.pageXOffset;
    sy = window.pageYOffset;
});

// Linear interpolation easing function
function li(a, b, n) {
    return (1 - n) * a + n * b;
}

// Animation loop
function render() {
    dx = li(dx, sx, 0.07);
    dy = li(dy, sy, 0.07);

    dx = Math.round(dx * 100) / 100;
    dy = Math.round(dy * 100) / 100;

    main.style.transform = `translate3d(${-dx}px, ${-dy}px, 0)`;

    requestAnimationFrame(render);
}
requestAnimationFrame(render);

// Smooth scroll for all in-page anchor links
document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
        const targetId = link.getAttribute('href').substring(1);
        const target = document.getElementById(targetId);
        if (target) {
            e.preventDefault();

            // Use offsetTop/offsetLeft for absolute position
            const targetY = target.offsetTop;
            const targetX = target.offsetLeft;

            // Update eased scroll targets
            sx = targetX;
            sy = targetY;
        }
    });
});
