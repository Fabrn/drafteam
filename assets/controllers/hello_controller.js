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

        document.querySelectorAll('[name="champion_grid_item"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const banButton = document.getElementById('btn-ban');
                const pickButton = document.getElementById('btn-pick');

                banButton.removeAttribute('disabled');
                pickButton.removeAttribute('disabled');

                banButton.setAttribute('data-live-id-param', checkbox.value);
                pickButton.setAttribute('data-live-id-param', checkbox.value);
            });
        });
    }

    connect() {
        if (this.urlValue) {
            const eventSource = new EventSource(this.urlValue);

            eventSource.onmessage = e => {
                const json = JSON.parse(e.data);
                console.log(json);

                this.component.render();
            };
        }
    }
}
