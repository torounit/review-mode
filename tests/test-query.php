<?php
/**
 * Class Query Test
 *
 * @package Review_Mode
 */

namespace Review_Mode;

/**
 * Sample test case.
 */
class Test_Query extends \WP_UnitTestCase {

	private $author;
	private $editor;
	private $subscriber;
	private $administrator;

	function setUp() {
		parent::setUp();
		$this->subscriber    = self::factory()->user->create_and_get(
			array(
				'role' => 'subscriber',
			)
		);
		$this->author        = self::factory()->user->create_and_get(
			array(
				'role' => 'author',
			)
		);
		$this->editor        = self::factory()->user->create_and_get(
			array(
				'role' => 'editor',
			)
		);
		$this->administrator = self::factory()->user->create_and_get(
			array(
				'role' => 'administrator',
			)
		);

		register_post_type( 'foo', [ 'public' => true ] );
	}

	public function post_type_provider() {
		return [
			[ 'post' ],
			[ 'page' ],
			[ 'foo' ],
		];
	}

	private $public  = 1;
	private $pending = 2;
	private $draft   = 4;
	private $private = 8;

	/**
	 * Create Posts for test.
	 *
	 * @param string $post_type post type name.
	 */
	function create_posts( $post_type ) {
		self::factory()->post->create_many(
			$this->public,
			array(
				'post_status' => 'publish',
				'post_type'   => $post_type,
				'post_author' => $this->author->ID,
			)
		);
		self::factory()->post->create_many(
			$this->pending,
			array(
				'post_status' => 'pending',
				'post_type'   => $post_type,
				'post_author' => $this->author->ID,
			)
		);
		self::factory()->post->create_many(
			$this->draft,
			array(
				'post_status' => 'draft',
				'post_type'   => $post_type,
				'post_author' => $this->author->ID,
			)
		);
		self::factory()->post->create_many(
			$this->private,
			array(
				'post_status' => 'private',
				'post_type'   => $post_type,
				'post_author' => $this->author->ID,
			)
		);
	}

	/**
	 * @dataProvider post_type_provider
	 *
	 * @param $post_type
	 */
	public function test_get_posts_with_subscriber( $post_type ) {
		$this->create_posts( $post_type );

		wp_set_current_user( $this->subscriber->ID );
		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
			)
		);
		$this->assertEquals( $this->public, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'publish',
			)
		);
		$this->assertEquals( $this->public, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'pending',
			)
		);
		$this->assertEquals( $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'draft',
			)
		);
		$this->assertEquals( $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'private',
			)
		);
		$this->assertEquals( $this->private, count( $query->posts ) );
	}

	/**
	 * @dataProvider post_type_provider
	 *
	 * @param $post_type
	 */
	public function test_get_posts_with_subscriber_with_review_mode( $post_type ) {
		$this->create_posts( $post_type );

		update_user_meta( $this->subscriber->ID, \Review_Mode\Options::META_KEY, true );
		wp_set_current_user( $this->subscriber->ID );
		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
			)
		);
		$this->assertEquals( $this->public + $this->pending + $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'publish',
			)
		);
		$this->assertEquals( $this->public + $this->pending + $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'pending',
			)
		);
		$this->assertEquals( $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'draft',
			)
		);
		$this->assertEquals( $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'private',
			)
		);
		$this->assertEquals( $this->private, count( $query->posts ) );
	}

	/**
	 * @dataProvider post_type_provider
	 *
	 * @param $post_type
	 */
	public function test_get_posts_with_editor( $post_type ) {
		$this->create_posts( $post_type );
		wp_set_current_user( $this->editor->ID );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
			)
		);

		$this->assertEquals( $this->public + $this->private, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'publish',
			)
		);
		$this->assertEquals( $this->public, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'pending',
			)
		);
		$this->assertEquals( $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'draft',
			)
		);
		$this->assertEquals( $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'private',
			)
		);
		$this->assertEquals( $this->private, count( $query->posts ) );
	}

	/**
	 * @dataProvider post_type_provider
	 *
	 * @param $post_type
	 */
	public function test_get_posts_with_editor_with_review_mode( $post_type ) {
		$this->create_posts( $post_type );

		update_user_meta( $this->editor->ID, \Review_Mode\Options::META_KEY, true );
		wp_set_current_user( $this->editor->ID );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
			)
		);
		$this->assertEquals( $this->public + $this->pending + $this->draft + $this->private, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'publish',
			)
		);
		$this->assertEquals( $this->public + $this->pending + $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'pending',
			)
		);
		$this->assertEquals( $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'draft',
			)
		);
		$this->assertEquals( $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => - 1,
				'post_status' => 'private',
			)
		);
		$this->assertEquals( $this->private, count( $query->posts ) );
	}
}
