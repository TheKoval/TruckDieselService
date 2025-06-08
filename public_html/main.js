document.addEventListener('DOMContentLoaded', function () {
    console.log('Скрипт main.js успешно подключен!');











    // ==== Логика бургер-меню ====
    const burger = document.getElementById('burger');
    const sideMenu = document.getElementById('sideMenu');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('closeBtn');
    
    if (burger && sideMenu && overlay && closeBtn) {
        burger.addEventListener('click', () => {
            sideMenu.classList.add('active');
            overlay.classList.add('active');
        });

        closeBtn.addEventListener('click', () => {
            sideMenu.classList.remove('active');
            overlay.classList.remove('active');
        });

        document.addEventListener('click', (event) => {
            if (!sideMenu.classList.contains('active')) return;

            const isClickInsideMenu = sideMenu.contains(event.target);
            const isClickOnBurger = burger.contains(event.target);

            if (!isClickInsideMenu && !isClickOnBurger) {
                sideMenu.classList.remove('active');
                overlay.classList.remove('active');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                sideMenu.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    } else {
        console.warn("Один из элементов бургер-меню не найден!");
    }

    // ==== Отправка формы ====
    const form = document.getElementById('diagnosticsForm');

    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch('send_email.php', {
                    method: 'POST',
                    body: formData
                });

                let result;
                try {
                    result = await response.json(); // Получаем JSON
                } catch (jsonError) {
                    console.error('Ошибка парсинга JSON:', jsonError);
                    alert('Произошла ошибка при получении ответа от сервера.');
                    return;
                }

                if (response.ok && result.status === 'success') {
                    form.innerHTML = `
    <div class="success-message" style="text-align:center;">
        <h2>Спасибо! Ваша заявка отправлена.</h2>
        <p>Свяжемся с вами в течение 1 рабочего дня</p>
    </div>
`;
                    form.reset();
                } else {
                    alert(`Ошибка: ${result.message || 'Неизвестная ошибка'}`);
                }
            } catch (networkError) {
                console.error('Ошибка сети:', networkError);
                alert('Не удалось отправить заявку. Проверьте интернет и попробуйте позже.');
            }
        });
    } 




// ==== Отправка формы "Заказ запчастей" ====
const formParts = document.getElementById('partsOrderForm');

if (formParts) {
    formParts.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(formParts);

        try {
            const response = await fetch('send_email_parts.php', {
                method: 'POST',
                body: formData
            });

            let result;
            try {
                result = await response.json(); // Получаем JSON
            } catch (jsonError) {
                console.error('Ошибка парсинга JSON:', jsonError);
                alert('Произошла ошибка при получении ответа от сервера.');
                return;
            }

            if (response.ok && result.status === 'success') {
                // Заменяем форму на сообщение об успехе
                formParts.innerHTML = `
                    <div class="success-message" style="text-align:center;">
                        <h2>Спасибо! Заявка получена!</h2>
                        <p>Наши специалисты свяжутся с вами в ближайшее рабочее время для уточнения деталей заказа.</p>
                    </div>
                `;
                formParts.reset();
            } else {
                alert(`Ошибка: ${result.message || 'Неизвестная ошибка'}`);
            }
        } catch (networkError) {
            console.error('Ошибка сети:', networkError);
            alert('Не удалось отправить заявку. Проверьте интернет и попробуйте позже.');
        }
    });
}





 // ==== Выделение активной вкладки ====
 const currentPath = window.location.pathname.split('/').pop(); // Например: index.html
 const navLinks = document.querySelectorAll('.menu a.text-button');
 const sideMenuLinks = document.querySelectorAll('.side-menu a');

 navLinks.forEach(link => {
     if (link.getAttribute('href') === currentPath) {
         link.classList.add('active');
     }
 });

 sideMenuLinks.forEach(link => {
     if (link.getAttribute('href') === currentPath) {
         link.classList.add('active');
     }
 });




    
});