# Job Board with Advanced Filtering

A Laravel application that manages job listings with complex filtering capabilities similar to Airtable. The application handles different job types with varying attributes using Entity-Attribute-Value (EAV) design patterns alongside traditional relational database models.

## Features

- Core Job model with standard fields
- Many-to-many relationships for Languages, Locations, and Categories
- Entity-Attribute-Value (EAV) system for dynamic attributes
- Advanced filtering API with complex query capabilities
- RESTful API endpoints

## Installation

1. Clone the repository
```bash
git clone <repository-url>
```

2. Install dependencies
```bash
composer install
```

3. Configure environment variables
```bash
cp .env.example .env
# Edit the .env file with your database credentials
```
5. Run migrations and seed the database
```bash
php artisan migrate --seed
```
6. Start the development server
```bash
php artisan serve
```
[Postman Collection](https://planetary-resonance-489164.postman.co/workspace/My-Workspace~81a166c3-8068-4465-8a04-03fcd1da3cd5/collection/18541507-e4ba8fc9-85cb-4094-af32-ee6cd5b636a3?action=share&creator=18541507)
## API Documentation

### Get Jobs with Filtering

**Endpoint:** `GET /api/jobs`

**Query Parameters:**
- `filter`: Filter expression for querying jobs
- `per_page`: Number of results per page (default: 15)

### Filter Syntax

The filtering system supports a powerful query syntax that allows for complex conditions and logical operations.

#### Basic Operators

- `=`: Equal
- `!=`: Not equal
- `>`: Greater than
- `<`: Less than
- `>=`: Greater than or equal
- `<=`: Less than or equal
- `LIKE`: Contains (for text fields)
- `IN`: Value in list
- `HAS_ANY`: Has any of the values in list
- `IS_ANY`: Relationship matches any of the values
- `EXISTS`: Relationship exists

#### Field Types

1. **Standard Fields**
   - `title`, `description`, `company_name`: Text fields
   - `salary_min`, `salary_max`: Numeric fields
   - `is_remote`: Boolean field
   - `job_type`, `status`: Enum fields
   - `published_at`, `created_at`, `updated_at`: Date fields

2. **Relationship Fields**
   - `languages`: Programming languages required for the job
   - `locations`: Possible locations for the job
   - `categories`: Job categories/departments

3. **Dynamic Attributes**
   - Access with `attribute:name` syntax
   - Supports text, number, boolean, date, and select types

#### Logical Operations

- `AND`: Logical AND
- `OR`: Logical OR
- Parentheses `()` for grouping conditions

### Example Queries

1. Find full-time jobs that require PHP or JavaScript:
```
/api/jobs?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript)))
```

2. Find jobs in New York or Remote locations with at least 3 years of experience:
```
/api/jobs?filter=(locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3
```

3. Find jobs that offer equity and have senior seniority:
```
/api/jobs?filter=attribute:has_equity=true AND attribute:seniority=Senior
```

4. Find remote jobs with salary above 100K:
```
/api/jobs?filter=is_remote=true AND salary_min>=100000
```

5. Find jobs published in the last week with application deadline in the future:
```
/api/jobs?filter=published_at>=2025-03-28 AND attribute:application_deadline>2025-04-04
```

## Database Schema

### Core Tables
- `jobs`: Main job listings
- `languages`: Programming languages
- `locations`: Job locations
- `categories`: Job categories
- `job_language`: Pivot table for jobs and languages
- `job_location`: Pivot table for jobs and locations
- `job_category`: Pivot table for categories and jobs

### EAV Tables
- `attributes`: Attribute definitions
- `job_attribute_values`: Attribute values for jobs

## Design Decisions and Tradeoffs

1. **Entity-Attribute-Value (EAV) Pattern**
   - **Pro**: Allows for flexible attributes per job type
   - **Con**: More complex queries compared to fixed columns
   - **Mitigation**: Indexing on attribute_id and value columns

2. **Filter String Parsing**
   - **Pro**: Clean, expressive query syntax
   - **Con**: Parser complexity increases with more operators
   - **Mitigation**: Structured parser with clear validation

3. **Relationship Filtering**
   - **Pro**: Powerful filtering across related entities
   - **Con**: Can lead to performance issues with large datasets
   - **Mitigation**: Eager loading relationships and indexing foreign keys

4. **Query Efficiency**
   - Used indexes on commonly filtered columns
   - Eager loading of relationships to avoid N+1 query problems
   - Optimized subqueries for relationship filtering

## Possible Improvements

1. Add caching for frequent filter combinations
2. Implement full-text search for text fields
3. Add more sophisticated pagination with cursor-based pagination
4. Support for sorting/ordering results
5. Implement GraphQL API for more flexible querying
6. Add rate limiting and authentication to API endpoints
