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
    }

    connect() {
        if (this.urlValue) {
            const eventSource = new EventSource(this.urlValue);

            eventSource.onmessage = e => {
                console.log(JSON.parse(e.data));

                this.component.render();
            };
        }
    }
}
