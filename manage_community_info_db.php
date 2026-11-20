<?php
require_once __DIR__."/admin_db.php";
$communityError = "";
$communityEdit = null;
$joinError = "";

if ( $_SERVER["REQUEST_METHOD"] === "POST" && in_array(
		$_POST["action"] ?? "", [ "join_approve", "join_reject" ], true
	) ) {
	adminCheckPost();
	$joinId = filter_var($_POST["join_id"] ?? null, FILTER_VALIDATE_INT);
	try {
		if ( !$joinId ) throw new RuntimeException("Invalid join request.");
		$pdo -> beginTransaction();
		$findJoin = $pdo -> prepare(
			"SELECT community_id FROM community_member WHERE id=? AND status='pending' FOR UPDATE"
		);
		$findJoin -> execute([ $joinId ]);
		$join = $findJoin -> fetch();
		if ( !$join ) throw new RuntimeException("Only pending join requests can be reviewed.");
		$status = $_POST["action"] === "join_approve" ? "approved" : "rejected";
		$updateJoin = $pdo -> prepare("UPDATE community_member SET status=? WHERE id=? AND status='pending'");
		$updateJoin -> execute([ $status, $joinId ]);
		if ( $status === "approved" ) {
			$increaseCount = $pdo -> prepare("UPDATE community_info SET member_count=member_count+1 WHERE id=?");
			$increaseCount -> execute([ $join["community_id"] ]);
		}
		$pdo -> commit();
		adminRedirect("manage_community.php?tab=join", "Join request ".$status.".");
	} catch ( RuntimeException|PDOException $exception ) {
		if ( $pdo -> inTransaction() ) $pdo -> rollBack();
		$joinError = $exception instanceof PDOException ? "Unable to review join request." : $exception -> getMessage();
	}
}

if ( $_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "member_remove" ) {
	adminCheckPost();
	$memberId = filter_var($_POST["member_id"] ?? null, FILTER_VALIDATE_INT);
	try {
		if ( !$memberId ) throw new RuntimeException("Invalid community member.");
		$pdo -> beginTransaction();
		$findMember = $pdo -> prepare(
			"SELECT community_id FROM community_member WHERE id=? AND status='approved' FOR UPDATE"
		);
		$findMember -> execute([ $memberId ]);
		$member = $findMember -> fetch();
		if ( !$member ) throw new RuntimeException("Community member not found.");
		$decreaseCount = $pdo -> prepare(
			"UPDATE community_info SET member_count=member_count-1 WHERE id=? AND member_count>0"
		);
		$decreaseCount -> execute([ $member["community_id"] ]);
		if ( $decreaseCount -> rowCount() !== 1 ) throw new RuntimeException(
			"Unable to update community member count."
		);
		$removeMember = $pdo -> prepare("DELETE FROM community_member WHERE id=? AND status='approved'");
		$removeMember -> execute([ $memberId ]);
		if ( $removeMember -> rowCount() !== 1 ) throw new RuntimeException("Community member not found.");
		$pdo -> commit();
		adminRedirect("manage_community.php?tab=join", "Community member removed.");
	} catch ( RuntimeException|PDOException $exception ) {
		if ( $pdo -> inTransaction() ) $pdo -> rollBack();
		$joinError = $exception instanceof PDOException ? "Unable to remove community member." : $exception -> getMessage();
	}
}

if ( $_SERVER["REQUEST_METHOD"] === "POST" && in_array(
		$_POST["action"] ?? "",
		[ "community_add", "community_edit", "community_delete" ], true
	) ) {
	adminCheckPost();
	$communityAction = $_POST["action"];
	$communityId = filter_var($_POST["community_id"] ?? null, FILTER_VALIDATE_INT);
	try {
		if ( $communityAction === "community_delete" ) {
			if ( !$communityId ) throw new RuntimeException("Invalid community.");
			$pdo -> beginTransaction();
			$check = $pdo -> prepare("SELECT id FROM community_info WHERE id=? FOR UPDATE");
			$check -> execute([ $communityId ]);
			if ( !$check -> fetch() ) throw new RuntimeException("Community not found.");
			$check = $pdo -> prepare(
				"SELECT (SELECT COUNT(*) FROM community_table WHERE community_id=?) AS posts, (SELECT COUNT(*) FROM community_member WHERE community_id=?) AS members"
			);
			$check -> execute([ $communityId, $communityId ]);
			$counts = $check -> fetch();
			if ( $counts["posts"] || $counts["members"] ) throw new RuntimeException(
				"Remove this community's posts and members before deleting it."
			);
			$delete = $pdo -> prepare("DELETE FROM community_info WHERE id=?");
			$delete -> execute([ $communityId ]);
			$pdo -> commit();
			adminRedirect("manage_community.php?tab=create", "Community deleted.");
		}
		$title = trim($_POST["community_title"] ?? "");
		$description = trim($_POST["community_description"] ?? "");
		if ( $title === "" || strlen($title) > 100 ) throw new RuntimeException(
			"Enter a community name of up to 100 characters."
		);
		if ( $communityAction === "community_add" ) {
			$save = $pdo -> prepare(
				"INSERT INTO community_info (title,description,member_count,created_at) VALUES (?,?,0,CURDATE())"
			);
			$save -> execute([ $title, $description ?: null ]);
		} else {
			if ( !$communityId ) throw new RuntimeException("Invalid community.");
			$check = $pdo -> prepare("SELECT id FROM community_info WHERE id=?");
			$check -> execute([ $communityId ]);
			if ( !$check -> fetch() ) throw new RuntimeException("Community not found.");
			$save = $pdo -> prepare("UPDATE community_info SET title=?,description=? WHERE id=?");
			$save -> execute([ $title, $description ?: null, $communityId ]);
		}
		adminRedirect("manage_community.php?tab=create", "Community saved.");
	} catch ( RuntimeException|PDOException $exception ) {
		if ( $pdo -> inTransaction() ) $pdo -> rollBack();
		$communityError = $exception instanceof PDOException ? "Unable to save community. Please check the details." : $exception -> getMessage();
	}
}
if ( isset($_GET["community_edit"]) ) {
	$editStatement = $pdo -> prepare("SELECT id,title,description FROM community_info WHERE id=?");
	$editStatement -> execute([ $_GET["community_edit"] ]);
	$communityEdit = $editStatement -> fetch() ?: null;
}
$communityRows = $pdo -> query(
	"SELECT community.id,community.title,community.description,community.member_count,community.created_at,(SELECT COUNT(*) FROM community_table AS post WHERE post.community_id=community.id) AS post_count,(SELECT COUNT(*) FROM community_member AS member WHERE member.community_id=community.id) AS membership_count FROM community_info AS community ORDER BY community.id DESC"
)
	-> fetchAll();
$joinRows = $pdo -> query(
	"SELECT member.id,member.email,member.community_id,community.title AS community_title,user.first_name,user.last_name FROM community_member AS member JOIN community_info AS community ON community.id=member.community_id JOIN user_table AS user ON user.email=member.email WHERE member.status='pending' ORDER BY member.id DESC"
)
	-> fetchAll();
$memberRows = $pdo -> query(
	"SELECT member.id,member.email,member.community_id,community.title AS community_title,user.first_name,user.last_name FROM community_member AS member JOIN community_info AS community ON community.id=member.community_id JOIN user_table AS user ON user.email=member.email WHERE member.status='approved' ORDER BY member.id DESC"
)
	-> fetchAll();
