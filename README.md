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

### 1. Start Docker Services

Run the following command to start the required Docker services (e.g., MySQL):

```sh
docker-compose up -d