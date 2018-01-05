<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 05.01.2018
 * Time: 16:41
 */

namespace anu\records;

use anu\base\Element;
use anu\db\ActiveQueryInterface;
use anu\db\ActiveRecord;
use anu\models\Section;


/**
 * Class Entry record.
 *
 * @property int                   $id         ID
 * @property int                   $sectionId  Section ID
 * @property int                   $typeId     Type ID
 * @property int                   $authorId   Author ID
 * @property \DateTime             $postDate   Post date
 * @property \DateTime             $expiryDate Expiry date
 * @property \anu\base\Element     $element    Element
 * @property \anu\models\Section   $section    Section
 * @property \anu\models\EntryType $type       Type
 * @property \anu\elements\User    $author     Author
 *
 * @author Robin Schambach
 */
class EntryRecord extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%entries}}';
    }

    /**
     * Returns the entry’s element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(ElementRecord::class, ['id' => 'id']);
    }

    /**
     * Returns the entry’s section.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getSection(): ActiveQueryInterface
    {
        return $this->hasOne(SectionRecord::class, ['id' => 'sectionId']);
    }

    /**
     * Returns the entry’s type.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getType(): ActiveQueryInterface
    {
        return $this->hasOne(EntryTypeRecord::class, ['id' => 'typeId']);
    }

    /**
     * Returns the entry’s author.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getAuthor(): ActiveQueryInterface
    {
        return $this->hasOne(UserRecord::class, ['id' => 'authorId']);
    }
}
