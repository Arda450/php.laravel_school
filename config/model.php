<?php

namespace Config;

use WendellAdriel\Lift\Lift;

use Illuminate\Database\Eloquent\Model as BaseModel;
/**
 * @mixin \Eloquent
 *
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 */
class Model extends BaseModel {
  use Lift;

  protected static $unguarded = true;
}
