let form = {
    sum: document.getElementById('sum'),
    submit: document.getElementById('btn-submit'),
    errorMessages: document.getElementById('error-messages'),
    message: document.getElementById('message'),
};
let payments = {
    initialSum: document.getElementById('initial-sum'),
    percent: document.getElementById('percent'),
    interestSum: document.getElementById('interest-sum'),
    paymentList: document.getElementById('payment-list'),
    paymentBlock: document.getElementById('payment-block'),
};

form.submit.addEventListener('click', () => {
    form.sum.value = Math.round(form.sum.value / 100) * 100;

    const requestData = JSON.stringify({
        'sum': form.sum.value,
    });
    const handleResponse = function (responseObject) {
        updateErrorMessages(responseObject.errors);
        if (responseObject.message) {
            addMessage(responseObject.message);
            while (payments.paymentList.firstChild) {
                payments.paymentList.removeChild(payments.paymentList.firstChild);
            }
            if (responseObject.data) {
                payments.paymentBlock.style.display = 'block';
                payments.initialSum.textContent = responseObject.data.initial_sum;
                payments.percent.textContent = responseObject.data.percent;
                payments.interestSum.textContent = responseObject.data.interest_sum;
                responseObject.data.payments.forEach((payment) => {
                    let tr = document.createElement('tr');
                    tr.innerHTML = `<td>${payment.date}</td><td>${payment.payment} €</td>`;
                    payments.paymentList.appendChild(tr);
                });
            }
        }
    }
    makeRequest('post', handleResponse, requestData);
});

function makeRequest(method, handleResponse, requestData = '') {
    let request = new XMLHttpRequest();
    request.onload = () => {
        let responseObject = null;

        try {
            responseObject = JSON.parse(request.responseText);
        } catch (e) {
            console.error('Could not parse JSON!');
            updateErrorMessages(['Nenumatyta klaida!']);

            return;
        }

        if (responseObject) {
            handleResponse(responseObject);
        }
    };

    request.open(method, 'api/investments');
    if (requestData) {
        request.setRequestHeader('Content-type', 'application/json');
        request.send(requestData);
    } else {
        request.send();
    }

}

function addMessage(message) {
    form.message.textContent = message;
    form.message.style.display = 'block';
}

function clearMessages() {
    while (form.errorMessages.firstChild) {
        form.errorMessages.removeChild(form.errorMessages.firstChild);
    }
    form.errorMessages.style.display = 'none';
    form.message.style.display = 'none';
}

function updateErrorMessages(errors) {
    clearMessages();
    if (errors) {
        errors.forEach((error) => {
            let li = document.createElement('li');
            li.textContent = error;
            form.errorMessages.appendChild(li);
        });

        form.errorMessages.style.display = 'block';
    }
}