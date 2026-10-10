// MEDEX — Envío del formulario de presupuestos.
// Puerto liviano (sin jQuery) de la lógica de ftp-original/presupuestos.js:
// valida campos requeridos + formato de email, y hace POST a mail/presupuesto_envio.php.
(function () {
  var form = document.getElementById('form-presupuesto');
  if (!form) return;

  var status = document.getElementById('form-presupuesto-status');
  var submitBtn = form.querySelector('button[type="submit"]');
  var submitLabel = submitBtn.textContent;

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    status.textContent = '';
    status.className = 'form-status';

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length === 0) {
      status.textContent = 'Por favor, validá el reCAPTCHA antes de enviar.';
      status.classList.add('form-status--error');
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Enviando...';

    fetch(form.action, {
      method: 'POST',
      body: new FormData(form)
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Respuesta no exitosa del servidor');
        }
        return response.text();
      })
      .then(function () {
        status.textContent = '¡Gracias! Recibimos tu solicitud y te vamos a contactar a la brevedad.';
        status.classList.add('form-status--success');
        form.reset();
      })
      .catch(function () {
        status.textContent = 'Hubo un problema al enviar el formulario. Por favor, intentá nuevamente o escribinos a info@medicallexpress.com.ar.';
        status.classList.add('form-status--error');
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = submitLabel;
        if (typeof grecaptcha !== 'undefined') {
          grecaptcha.reset();
        }
      });
  });
})();
