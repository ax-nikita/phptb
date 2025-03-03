const data_table = document.getElementById("data");
const form = document.getElementById("form"); // Поменял фокус события с кнопки на форму, так будет логичнее

form.onsubmit = function (e) {
  e.preventDefault();
  data_table.style.display = "block";

  let
      xhr = new XMLHttpRequest(),
      form_data = new FormData(form), // Вместо селекторов использую класс FormData, так в дальнейшем можно будет менять html не меняя скрипты
      url = form.getAttribute('action'), // Для xhr так-же использую параметры указанные для формы в html
      search_param = new URLSearchParams(form_data).toString(); // Формирую строку с параметрами для GET

  xhr.open('get', url + '?' + search_param, true);

  xhr.onload = () => {
    if (xhr.status != 200) {
      alert(`Error ${xhr.status}: ${xhr.statusText}`);
    } else {
      // Находим имя пользователя и прописываем его над таблицей
      let
          user_select = form.querySelector('[name="user"]');

      data_table.querySelector('[name="user"]').innerText = user_select.querySelector('[value="' + user_select.value + '"]').innerText;

      let
          data = JSON.parse(xhr.response),
          rows = data_table.querySelectorAll('tr[data-id]'); // Выбираем строки месяцев, все месяца указаны в $month_names

      rows.forEach((el) => {
        let
            month = el.getAttribute('data-id'),
            month_data,
            amount = 0,
            count = 0;

        month_data = data.find((element) => { // Если в полученной data не будет нужного нам месяца то значения суммы и кол-ва будут 0
          return element.month == month;
        })

        if (month_data !== undefined) {
          amount = month_data.amount;
          count = month_data.count;
        }

        el.querySelector('[name="amount"]').innerText = amount;
        el.querySelector('[name="count"]').innerText = count;
      });
    }
  };

  xhr.send(form_data);
};
