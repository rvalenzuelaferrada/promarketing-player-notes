<?php

namespace App\Models;

use Database\Factories\PlayerNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $player_id
 * @property int $author_id
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $player
 * @property-read User $author
 */
#[Fillable(['player_id', 'author_id', 'content'])]
class PlayerNote extends Model
{
    /** @use HasFactory<PlayerNoteFactory> */
    use HasFactory;

    /**
     * The player (user) the note refers to.
     *
     * @return BelongsTo<User, $this>
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    /**
     * The support agent (user) who authored the note.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
