document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.ConsultForm');
    if (!form) return;

    const ErrorMessage = document.getElementById('ErrorMessage');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (ErrorMessage) ErrorMessage.textContent = '';

        const firstName = document.getElementById('FirstName').value.trim();
        const lastName = document.getElementById('LastName').value.trim();
        const email = document.getElementById('Email').value.trim();
        const phone = document.getElementById('Phone').value.trim();
        const postcode = document.getElementById('Postcode').value.trim();
        const reason = document.getElementById('Reason').value.trim();

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        const ukPhoneRegex = /^(?:(?:\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}|(?:\+44\s?1\d{3}|\(?01\d{3}\)?)\s?\d{3}\s?\d{3}|(?:\+44\s?2\d{2}|\(?02\d{2}\)?)\s?\d{4}\s?\d{4})$/;

        const ukPostcodeRegex = /^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i;

        if (!firstName || !lastName || !email || !phone || !postcode || !reason) {
            if (ErrorMessage) ErrorMessage.textContent = 'Please fill in all required fields.';
            return;
        }

        if (!emailRegex.test(email)) {
            if (ErrorMessage) ErrorMessage.textContent = 'Please enter a valid email address.';
            return;
        }

        if (!ukPhoneRegex.test(phone)) {
            if (ErrorMessage) ErrorMessage.textContent = 'Please enter a valid UK phone number.';
            return;
        }

        if (!ukPostcodeRegex.test(postcode)) {
            if (ErrorMessage) ErrorMessage.textContent = 'Please enter a valid UK postcode.';
            return;
        }

        form.submit();
    });
});
