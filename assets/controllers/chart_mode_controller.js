import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle', 'scrollArea', 'chart'];

    connect() {
        this.update();
    }

    update() {
        const extended = this.toggleTarget.checked;

        this.scrollAreaTargets.forEach((scrollArea) => {
            scrollArea.classList.toggle('overflow-x-auto', extended);
        });

        this.chartTargets.forEach((chart) => {
            const years = Number.parseInt(chart.dataset.years ?? '0', 10);
            chart.style.minWidth = extended ? `max(100%, ${years}rem)` : '100%';
        });

        requestAnimationFrame(() => {
            window.dispatchEvent(new Event('resize'));
        });
    }
}
