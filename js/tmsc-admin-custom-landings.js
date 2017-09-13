/**
 * Public Section Metabox Package
 * Allows you to easily add in edit links to show and hide inputs and updates any corresponding inputs.
 *
 * @param  {jQuery} jQuery
 */

jQuery(document).ready(($) => {
  const vm = this;

  /**
   * Get our edit buttons
   */
  vm.editButton = $('.misc-pub-section a.ai-edit-button');
  vm.saveButton = $('.misc-pub-section .ai-edit-button-target a.ai-save-button');
  vm.cancelButton = $('.misc-pub-section .ai-edit-button-target a.ai-cancel-button');

  /**
   * Callback to toggle display
   */
  vm.toggleInputDisplay = (event) => {
    const parentSection = $(event.target).closest('div.misc-pub-section');
    const editButton = parentSection.find('a.ai-edit-button');
    const targetInput = parentSection.find('.ai-edit-button-target');
    editButton.toggle();
    targetInput.toggle();
    event.preventDefault();
  };

  vm.setInput = (event) => {
    const parentSection = $(event.target).closest('div.misc-pub-section');
    const targetInput = parentSection.find('.ai-edit-button-target :input option:selected');
    parentSection.find('span.ai-target-text').html(targetInput.text());
    vm.toggleInputDisplay(event);
    event.preventDefault();
  };

  // Trigger term load on taxonomy change and page load
  vm.editButton.on('click', vm.toggleInputDisplay);
  vm.cancelButton.on('click', vm.toggleInputDisplay);
  vm.saveButton.on('click', vm.setInput);
});
