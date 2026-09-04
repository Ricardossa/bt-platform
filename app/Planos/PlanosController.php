<?php
declare(strict_types=1);

namespace BT\App\Planos;

use BT\Core\Database\Database;

final class PlanosController
{
    public function listar(): array
    {
        return Database::fetchAll("SELECT * FROM planos ORDER BY preco_mensal ASC");
    }

    public function buscar(int $id): ?array
    {
        return Database::fetch("SELECT * FROM planos WHERE id = ?", [$id]);
    }

    public function salvar(array $dados): bool
    {
        if (isset($dados['id']) && $dados['id'] > 0) {
            return Database::execute(
                "UPDATE planos SET nome = ?, slug = ?, preco_mensal = ?, taxa_setup = ?, max_dispositivos = ?, recursos = ?, status = ? WHERE id = ?",
                [
                    $dados['nome'],
                    $dados['slug'],
                    (float)$dados['preco_mensal'],
                    (float)$dados['taxa_setup'],
                    (int)$dados['max_dispositivos'],
                    $dados['recursos'],
                    $dados['status'],
                    (int)$dados['id']
                ]
            );
        }

        return Database::execute(
            "INSERT INTO planos (nome, slug, preco_mensal, taxa_setup, max_dispositivos, recursos, status) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $dados['nome'],
                $dados['slug'],
                (float)$dados['preco_mensal'],
                (float)$dados['taxa_setup'],
                (int)$dados['max_dispositivos'],
                $dados['recursos'],
                $dados['status']
            ]
        );
    }

    public function excluir(int $id): bool
    {
        return Database::execute("DELETE FROM planos WHERE id = ?", [$id]);
    }
}
