# Gantt Charts

## Project Description

This project is a web-based application for managing tasks using a Gantt chart. It allows users to create, update, delete, and filter tasks visually. The application is built with Laravel for the backend and Vue.js for the frontend, utilizing D3.js for rendering the Gantt chart. It also includes Docker support for easy setup and deployment.

## Features

- Create, update, and delete tasks.
- Visualize tasks on a Gantt chart.
- Filter tasks by day, week, or month.
- Responsive and user-friendly interface.

## Prerequisites

Before running the project, ensure you have the following installed:

- Docker and Docker Compose
- PHP (>= 8.2)
- Composer
- Node.js and npm

## Commands to Run the Project

Follow these steps to set up and run the project:

#### Docker

1. use the command `docker-compose up -d`

#### Laravel

1. Navigate the project folder
2. Run the Laravel development server: `php artisan serve`

#### Start Vite for Frontend Development

1. Run the command `npm run dev`

##### Notes

- If we get the "Vite Manifest Not Found"

``` rm -rf node_modules package-lock.json public/build
npm cache clean --force
npm install
npm run build
php artisan serve
```

- If you get any Database Connection Errors

```
// make sure Docker is running
docker ps
//Restart the MySQL container
docker-compose restart
//if database migrations are needed
php artisan migrate --seed
```