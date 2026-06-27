import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static values = {
        url: String,
    };

    async initialize() {
        this.component = await getComponent(this.element);

        const banButton = document.getElementById('btn-ban');

        document.querySelectorAll('[name="champion_grid_item"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                banButton.removeAttribute('disabled');
            });
        });

        banButton.addEventListener('click', () => {
            this.component.action('ban', {
                id: document.querySelector('[name="champion_grid_item"]:checked').getAttribute('data-id'),
            });
        });
    }

    connect() {
        if (this.urlValue) {
            const eventSource = new EventSource(this.urlValue);

            eventSource.onmessage = e => {
                const json = JSON.parse(e.data);

                if ('ban' === json.action) {
                    window.alert('Ban ' + json.championName);
                }

                this.component.render();
            };
        }
    }
}
