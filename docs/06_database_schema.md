# Database Schema

## Table: events

Fields:

```
id
type
severity
lat
lng
location
description
source
timestamp
created_at
updated_at
```

---

## Example Migration

```
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('type');
    $table->float('lat');
    $table->float('lng');
    $table->string('location')->nullable();
    $table->float('severity')->nullable();
    $table->string('source')->nullable();
    $table->timestamp('timestamp');
    $table->timestamps();
});
```

