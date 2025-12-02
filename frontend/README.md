# Vue.js Frontend

Базовый Vue.js проект с Vite для работы с Laravel бэкендом.

## Технологии

- **Vue.js 3** - Прогрессивный JavaScript фреймворк
- **Vite** - Инструмент сборки следующего поколения
- **Vue Router** - Официальный маршрутизатор для Vue.js
- **Axios** - HTTP клиент для работы с API

## Установка

```bash
npm install
```

## Разработка

```bash
npm run dev
```

Приложение будет доступно по адресу `http://localhost:5173`

## Сборка

```bash
npm run build
```

Собранные файлы будут в директории `dist/`

## Переменные окружения

Создайте файл `.env` на основе `.env.example`:

```env
VITE_API_URL=http://localhost/api
VITE_ADMIN_URL=http://localhost/admin
```

## Структура проекта

```
frontend/
├── src/
│   ├── components/    # Переиспользуемые компоненты
│   ├── views/         # Страницы приложения
│   ├── App.vue        # Корневой компонент
│   ├── main.js        # Точка входа
│   └── style.css      # Глобальные стили
├── index.html
├── vite.config.js
└── package.json
```


