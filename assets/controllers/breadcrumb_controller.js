import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['breadcrumb'];
    
    observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            return e.isIntersecting 
                ? e.target.classList.remove('border-bottom')
                : e.target.classList.add('border-bottom');
        })
    }, { threshold: [1] });

    connect() {
        this.observer.observe(this.breadcrumbTarget);
    }

    disconnect() {
        this.observer.unobserve(this.breadcrumbTarget);
    }
}