# Task Rabbit — Full-Stack Task Management App

Task Rabbit is a full-stack task management platform that lets users be assigned to projects and create, assign, update, and track tasks.  
It includes a **Vue.js frontend**, a **Laravel REST API backend**, and a **MySQL** database — all containerized using **Docker**.

This project is built as part of a development assignment and demonstrates a modern, scalable architecture with clean separation between frontend, backend, and database services.

---

## 📑 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
  - [Run with start script](#-run-with-start-script)
  - [Run without start script](#-run-without-script)
- [Unit Testing](#-unit-testing)
  - [Run locally](#-run-locally-docker-recommended)
  - [Run in container](#-run-in-container)
- [Database Schema](#-database-schema-overview)
- [Assumptions](#-assumptions)

---

## ✨ Features
- Manage Projects (Admin users)
- Assign users to Project (Admin user)
- Manage Users (Admin users)
- Create, update, and delete tasks inside a project (All users, delete is only for admins)
- Assign tasks to other users included in project (All user)
- Add comments to tasks (All users)
- Mark tasks as completed (All user)
- Close task (Admin users)
- View tasks assigned to you (All user)
- Analytics Page (Admin users) 
- RESTful API (CRUD)
- Frontend built with Vue
- Laravel backend with database migrations
- MySQL relational database
- Dockerized development environment
- Unit tests for backend API

---

## 🧱 Tech Stack

**Frontend**
- Vue.js  
- Vite  
- Axios

**Backend**
- PHP
- Laravel
- PHPUnit (unit tests)

**Database**
- MySQL

**Infrastructure**
- Docker & Docker Compose  
- Shell helper script (`start.sh`)

---

## 🗂 Project Structure

```txt
.
├── backend/              # Laravel API (routes, controllers, models, tests)
├── frontend/             # Vue (components, pages, API client)
├── docker-compose.yml    # Multi-container setup (frontend, backend, db)
└── start.sh              # Helper script to run the full stack
```

## 🚀 Getting Started
✅ Prerequisites

- Docker & Docker Compose installed

- Git installed

### ▶️ Run with start script
This is the easiest way to start everything.
This will build and up the containers as well as running the migrations.

```bash
git clone https://github.com/AtanasKr/task-rabbit
cd task-rabbit
chmod +x start.sh
./start.sh
```

### 🐳 Run without start script
#### 1. Clone repo
```bash
git clone https://github.com/AtanasKr/task-rabbit
```

#### 2. Backend env setup
```bash
cp backend/.env.example backend/.env
```

#### 3. Start containers
```bash
docker compose build --no-cache
docker compose up
```
Composer install should run automatically inside the backend container, and npm install should run inside the frontend container.
If not, please run them manually.
After containers are running:
```bash
php artisan migrate
```
inside the backend container or in the backend project folder.

## 🧪 Unit Testing
Backend unit tests are written using PHPUnit and Laravel’s testing tools.

### 1. Run locally
If you have PHP and Composer installed on your host and want to run tests directly:
```bash
cd backend
composer install  # if not already done
php artisan test --env=testing
# or:
./vendor/bin/phpunit
```

### 2. Run in container
```bash
# From project root
docker compose exec backend php artisan test --env=testing
```

## 🗄 Database Schema Overview

The database uses a relational MySQL design with users, projects, tasks, and comments linked through foreign keys.
It also includes system tables used by Laravel (jobs, cache, tokens, etc.).

### Main Tables

---

### **users**
Stores all user accounts.

- id  
- name  
- email  
- email_verified_at  
- password  
- role  
- remember_token  
- created_at / updated_at  

**Relations**
- Has many: tasks (created_by / assigned_to)  
- Has many: comments   
- Belongs to many: projects (through `project_members`)  

---

### **projects**
Represents a project.

- id  
- name  
- description  
- start_date  
- end_date  
- created_at / updated_at  

**Relations**
- Has many: tasks  
- Has many: project_members  
- Many-to-many: users (through `project_members`)  

---

### **project_members** (pivot)
Links users to projects.

- id  
- project_id  
- user_id  
- created_at / updated_at  

**Relations**
- Belongs to: projects  
- Belongs to: users  

---

### **tasks**
Represents a task inside a project.

- id  
- project_id  
- title  
- description  
- status_id  
- assigned_to  
- created_by  
- due_date  
- completed_at  
- created_at / updated_at  

**Relations**
- Belongs to: projects  
- Belongs to: users (assigned_to)  
- Belongs to: users (created_by)  
- Belongs to: task_statuses  
- Has many: comments  

---

### **task_statuses**
Lookup table for task statuses.

- id  
- name  
- sort_order  

**Relations**
- Has many: tasks  

---

### **comments**
User comments on tasks.

- id  
- task_id  
- user_id  
- body  
- created_at / updated_at  

**Relations**
- Belongs to: tasks  
- Belongs to: users  

---

## System Tables (Laravel Internal)

These tables support Laravel features and background jobs.  
They are not part of the domain model, but exist for framework functionality.

### **cache**
- key  
- value  
- expiration  

### **cache_locks**
- key  
- owner  
- expiration  

### **sessions**
- id  
- user_id  
- ip_address  
- user_agent  
- payload  
- last_activity  

### **jobs**
- id  
- queue  
- payload  
- attempts  
- reserved_at  
- available_at  
- created_at  

### **failed_jobs**
- id  
- uuid  
- connection  
- queue  
- payload  
- exception  
- failed_at  

### **job_batches**
- id  
- name  
- total_jobs  
- pending_jobs  
- failed_jobs  
- failed_job_ids  
- options  
- cancelled_at  
- created_at  
- finished_at  

### **personal_access_tokens**
- id  
- tokenable_type  
- tokenable_id  
- name  
- token  
- abilities  
- last_used_at  
- expires_at  
- created_at / updated_at  

### **migrations**
- id  
- migration  
- batch  

### **password_reset_tokens**
- email  
- token  
- created_at  

---

## 🧩 Assumptions
- Authentication is handled at the API level (Laravel auth sanctum).
- Only authenticated users can access the app and API.
- Admin flag is stored as user.role in the users table.
- Only admins can:
  - Manage users
  - Manage projects
  - Delete tasks
  - Close tasks
  - View the analytics page
- All regular users can:
  - View projects they are assigned to
  - Create and update tasks within those projects
  - Assign tasks only to users in the same project
  - Comment on tasks they can see
- Work flow consists of:
  - Registering as admin
  - As admin creating project
  - Assigning users for the project
  - Admin or user can now create and assign tasks for users in given project
