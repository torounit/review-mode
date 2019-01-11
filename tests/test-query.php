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

	/**
	 * Author
	 *
	 * @var \WP_User
	 */
	private $author;

	/**
	 * Editor
	 *
	 * @var \WP_User
	 */
	private $editor;

	/**
	 * Subscriber
	 *
	 * @var \WP_User
	 */
	private $subscriber;

	/**
	 * Administrator
	 *
	 * @var \WP_User
	 */
	private $administrator;

	/**
	 * Public post count.
	 *
	 * @var int
	 */
	private $public = 1;

	/**
	 * Pending post count.
	 *
	 * @var int
	 */
	private $pending = 2;

	/**
	 * Draft post count.
	 *
	 * @var int
	 */
	private $draft = 4;

	/**
	 * Private post count.
	 *
	 * @var int
	 */
	private $private = 8;

	/**
	 * Setup
	 */
	public function setUp() {
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

	/**
	 * Data provider for post types.
	 *
	 * @return array
	 */
	public function post_type_provider() {
		return [
			[ 'post' ],
			[ 'page' ],
			[ 'foo' ],
		];
	}

	/**
	 * Create Posts for test.
	 *
	 * @param string $post_type post type name.
	 */
	private function create_posts( $post_type ) {
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
	 * Subscriber user inactive review mode.
	 *
	 * @test
	 * @dataProvider post_type_provider
	 *
	 * @param string $post_type post type name.
	 */
	public function test_get_posts_with_subscriber( $post_type ) {
		$this->create_posts( $post_type );

		wp_set_current_user( $this->subscriber->ID );
		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
			)
		);
		$this->assertEquals( $this->public, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			)
		);
		$this->assertEquals( $this->public, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'pending',
			)
		);
		$this->assertEquals( $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'draft',
			)
		);
		$this->assertEquals( $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'private',
			)
		);
		$this->assertEquals( $this->private, count( $query->posts ) );
	}

	/**
	 * Subscriber user active review mode.
	 *
	 * @test
	 * @dataProvider post_type_provider
	 *
	 * @param string $post_type post type name.
	 */
	public function test_get_posts_with_subscriber_with_review_mode( $post_type ) {
		$this->create_posts( $post_type );

		update_user_meta( $this->subscriber->ID, \Review_Mode\Options::META_KEY, true );
		wp_set_current_user( $this->subscriber->ID );
		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
			)
		);
		$this->assertEquals( $this->public + $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			)
		);
		$this->assertEquals( $this->public + $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'pending',
			)
		);
		$this->assertEquals( $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'draft',
			)
		);
		$this->assertEquals( $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'private',
			)
		);
		$this->assertEquals( $this->private, count( $query->posts ) );
	}

	/**
	 * Editor user inactive review mode.
	 *
	 * @test
	 * @dataProvider post_type_provider
	 *
	 * @param string $post_type post type name.
	 */
	public function test_get_posts_with_editor( $post_type ) {
		$this->create_posts( $post_type );
		wp_set_current_user( $this->editor->ID );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
			)
		);

		$this->assertEquals( $this->public + $this->private, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			)
		);
		$this->assertEquals( $this->public, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'pending',
			)
		);
		$this->assertEquals( $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'draft',
			)
		);
		$this->assertEquals( $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'private',
			)
		);
		$this->assertEquals( $this->private, count( $query->posts ) );
	}

	/**
	 * Editor user active review mode.
	 *
	 * @test
	 * @dataProvider post_type_provider
	 *
	 * @param string $post_type post type name.
	 */
	public function test_get_posts_with_editor_with_review_mode( $post_type ) {
		$this->create_posts( $post_type );

		update_user_meta( $this->editor->ID, \Review_Mode\Options::META_KEY, true );
		wp_set_current_user( $this->editor->ID );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
			)
		);
		$this->assertEquals( $this->public + $this->pending + $this->private, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			)
		);
		$this->assertEquals( $this->public + $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'pending',
			)
		);
		$this->assertEquals( $this->pending, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'draft',
			)
		);
		$this->assertEquals( $this->draft, count( $query->posts ) );

		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'private',
			)
		);
		$this->assertEquals( $this->private, count( $query->posts ) );
	}
}
