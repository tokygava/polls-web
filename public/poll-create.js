'use strict';

const questions = document.querySelector('#questions');
const addQuestion = document.querySelector('#add-question');
const maxQuestions = Number(questions.dataset.maxQuestions);
const maxOptions = Number(questions.dataset.maxOptions);
const questionTemplate = questions.querySelector('[data-question]').cloneNode(true);
const optionTemplate = questionTemplate.querySelector('[data-option]').cloneNode(true);

function updateForm() {
    const fields = Array.from(questions.querySelectorAll('[data-question]'));
    addQuestion.disabled = fields.length >= maxQuestions;

    fields.forEach((field, index) => {
        const name = `questions[${index}]`;
        field.querySelector('legend').textContent = `Вопрос ${index + 1}`;
        field.querySelector('input').name = `${name}[prompt]`;

        const removeQuestion = field.querySelector('[data-action="remove-question"]');
        removeQuestion.hidden = false;
        removeQuestion.disabled = fields.length <= 1;

        const options = Array.from(field.querySelectorAll('[data-option]'));
        const addOption = field.querySelector('[data-action="add-option"]');
        addOption.hidden = false;
        addOption.disabled = options.length >= maxOptions;

        options.forEach((option, optionIndex) => {
            option.querySelector('span').textContent = `Вариант ${optionIndex + 1}`;
            option.querySelector('input').name = `${name}[options][]`;

            const removeOption = option.querySelector('[data-action="remove-option"]');
            removeOption.hidden = false;
            removeOption.disabled = options.length <= 2;
        });
    });
}

function newOption() {
    const option = optionTemplate.cloneNode(true);
    option.querySelector('input').value = '';
    return option;
}

addQuestion.addEventListener('click', () => {
    const field = questionTemplate.cloneNode(true);
    field.querySelector('input').value = '';
    field.querySelector('[data-options]').replaceChildren(newOption(), newOption());
    questions.append(field);
    updateForm();
    field.querySelector('input').focus();
});

questions.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button) {
        return;
    }

    const field = button.closest('[data-question]');
    let focusTarget;
    switch (button.dataset.action) {
        case 'add-option':
            const option = newOption();
            field.querySelector('[data-options]').append(option);
            focusTarget = option.querySelector('input');
            break;
        case 'remove-option':
            button.closest('[data-option]').remove();
            focusTarget = field.querySelector('[data-action="add-option"]');
            break;
        case 'remove-question':
            field.remove();
            focusTarget = addQuestion;
            break;
    }
    updateForm();
    focusTarget?.focus();
});

addQuestion.hidden = false;
updateForm();
