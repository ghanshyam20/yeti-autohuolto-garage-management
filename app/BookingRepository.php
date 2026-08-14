<?php

declare(strict_types=1);

namespace Yeti;

use PDO;

final class BookingRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param array<string, string> $data */
    public function create(array $data): int
    {
        $now = now();
        $statement = $this->pdo->prepare(
            'INSERT INTO bookings ('
            . 'full_name, phone_number, email, vehicle_make, vehicle_model, registration_number, '
            . 'service_required, problem_description, preferred_date, preferred_time, status, created_at, updated_at'
            . ') VALUES ('
            . ':full_name, :phone_number, :email, :vehicle_make, :vehicle_model, :registration_number, '
            . ':service_required, :problem_description, :preferred_date, :preferred_time, :status, :created_at, :updated_at'
            . ')'
        );

        $statement->execute([
            'full_name' => $data['full_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'],
            'vehicle_make' => $data['vehicle_make'],
            'vehicle_model' => $data['vehicle_model'],
            'registration_number' => $data['registration_number'],
            'service_required' => $data['service_required'],
            'problem_description' => $data['problem_description'],
            'preferred_date' => $data['preferred_date'],
            'preferred_time' => $data['preferred_time'],
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM bookings WHERE id = :id');
        $statement->execute(['id' => $id]);
        $booking = $statement->fetch();

        return $booking === false ? null : $booking;
    }

    /** @param array<string, string> $data */
    public function update(int $id, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE bookings SET '
            . 'full_name = :full_name, phone_number = :phone_number, email = :email, '
            . 'vehicle_make = :vehicle_make, vehicle_model = :vehicle_model, '
            . 'registration_number = :registration_number, service_required = :service_required, '
            . 'problem_description = :problem_description, preferred_date = :preferred_date, '
            . 'preferred_time = :preferred_time, status = :status, updated_at = :updated_at '
            . 'WHERE id = :id'
        );

        $statement->execute([
            'full_name' => $data['full_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'],
            'vehicle_make' => $data['vehicle_make'],
            'vehicle_model' => $data['vehicle_model'],
            'registration_number' => $data['registration_number'],
            'service_required' => $data['service_required'],
            'problem_description' => $data['problem_description'],
            'preferred_date' => $data['preferred_date'],
            'preferred_time' => $data['preferred_time'],
            'status' => $data['status'],
            'updated_at' => now(),
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM bookings WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function count(?string $status = null): int
    {
        if ($status === null) {
            return (int) $this->pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
        }

        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM bookings WHERE status = :status');
        $statement->execute(['status' => $status]);
        return (int) $statement->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function recent(int $limit = 5): array
    {
        $limit = max(1, min($limit, 100));
        return $this->pdo
            ->query("SELECT * FROM bookings ORDER BY created_at DESC, id DESC LIMIT {$limit}")
            ->fetchAll();
    }

    /**
     * @param array{search?: string, status?: string, service?: string, date?: string} $filters
     * @return array{items: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    public function paginate(array $filters, int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $parameters = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $searchColumns = ['full_name', 'phone_number', 'email', 'vehicle_make', 'vehicle_model', 'registration_number'];
            $searchParts = [];
            foreach ($searchColumns as $index => $column) {
                $parameter = 'search_' . $index;
                $searchParts[] = "{$column} LIKE :{$parameter}";
                $parameters[$parameter] = '%' . $search . '%';
            }
            $where[] = '(' . implode(' OR ', $searchParts) . ')';
        }

        foreach (['status', 'service_required' => 'service', 'preferred_date' => 'date'] as $column => $key) {
            if (is_int($column)) {
                $column = $key;
            }
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '') {
                $where[] = "{$column} = :{$key}";
                $parameters[$key] = $value;
            }
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $countStatement = $this->pdo->prepare('SELECT COUNT(*) FROM bookings' . $whereSql);
        $countStatement->execute($parameters);
        $total = (int) $countStatement->fetchColumn();

        $perPage = max(1, min($perPage, 100));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare(
            'SELECT * FROM bookings' . $whereSql
            . ' ORDER BY created_at DESC, id DESC LIMIT ' . $perPage . ' OFFSET ' . $offset
        );
        $statement->execute($parameters);

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }
}
