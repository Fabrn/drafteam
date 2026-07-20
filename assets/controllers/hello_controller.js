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
        const _this = this;

        this.component = await getComponent(this.element);

        document.querySelectorAll('[name="champion_grid_item"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const banButton = document.getElementById('btn-ban');
                const pickButton = document.getElementById('btn-pick');

                banButton.removeAttribute('disabled');
                pickButton.removeAttribute('disabled');

                banButton.setAttribute('data-live-id-param', checkbox.value);
                pickButton.setAttribute('data-live-id-param', checkbox.value);

                this.component.action('prePick', {
                    id: checkbox.value,
                });
            });
        });

        document.querySelectorAll('[class^="champion_grid_item"]').forEach(draggable => {
            draggable.addEventListener('dragstart', function (e) {
                e.dataTransfer.setData('text/plain', draggable.getAttribute('data-id'));
            });
        });

        document.querySelectorAll('[data-drop-action]').forEach(container => {
            container.addEventListener('dragover', function (e) {
                e.preventDefault();
            });

            container.addEventListener('drop', function (e) {
                e.preventDefault();

                const championId = Number.parseInt(e.dataTransfer.getData('text/plain'));

                _this.component.action('choose', {
                    championId,
                    action: container.getAttribute('data-drop-action'),
                    side: container.getAttribute('data-drop-side'),
                    index: container.getAttribute('data-drop-index'),
                });
            });

            container.addEventListener('contextmenu', function (e) {
                e.preventDefault();

                const dropzone = e.target.closest('[data-drop-action]');

                if (!dropzone) {
                    return;
                }

                _this.component.action('remove', {
                    action: dropzone.getAttribute('data-drop-action'),
                    side: dropzone.getAttribute('data-drop-side'),
                    index: dropzone.getAttribute('data-drop-index'),
                });
            });
        })
    }

    connect() {
        if (this.urlValue) {
            const eventSource = new EventSource(this.urlValue);

            eventSource.onmessage = e => {
                const json = JSON.parse(e.data);

                this.component.render();
            };
        }
    }
}
