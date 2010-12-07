<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Xavier Perseguers <typo3@perseguers.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * Lexical analyzer.
 *
 * The whole parser is based on compilation course (LAMP) I attended at
 * Swiss Federal Institute of Technology. Nice to use that again ;-)
 * @see http://lamp.epfl.ch/teaching/archive/compilation/2002/project/assignments/1/instructions_header_web.shtml
 *
 * $Id$
 *
 * @author Xavier Perseguers <typo3@perseguers.ch>
 * @package TYPO3
 * @subpackage dbal\sql
 */
class tx_dbal_sql_Scanner implements tx_dbal_sql_Tokens {

	/**
	 * Current lexeme
	 * @var integer
	 */
	public $token;

	/**
	 * Position of the first character of the current lexeme
	 * @var integer
	 */
	public $start;

	/**
	 * Representation of the current lexeme (only if the lexeme
	 * has multiple possible representations)
	 * @var string
	 */
	public $chars;

	/**
	 * Buffer to construct the lexeme's representation
	 * @var string
	 */
	protected $buffer;

	/**
	 * Current character
	 * @var string
	 */
	private $ch;

	/**
	 * Line for current character
	 * @var integer
	 */
	private $line = 1;

	/**
	 * Column for current character
	 * @var integer
	 */
	private $column = 0;

	/**
	 * @var tx_dbal_sql_Global
	 */
	protected $global;

	/**
	 * Input stream
	 * @var tx_dbal_System_Io_Reader
	 */
	private $in;

	/**
	 * Reserved for method {@see nextCh()}
	 * @var string
	 */
	private $oldCh;

	/**
	 * Default constructor
	 *
	 * @param tx_dbal_System_Io_Reader $in Input stream
	 */
	public function __construct(tx_dbal_sql_Global $global, tx_dbal_System_Io_Reader $in) {
		$this->global = $global;
		$this->in = $in;
		$this->buffer = '';
		$this->nextCh();
		$this->nextToken();
	}

	/**
	 * Reads the next lexeme (by removing all white spaces and comments
	 * preceding it) and stores its representation (if not unique) into
	 * $this->chars and its class into $this->token.
	 *
	 * @return void
	 */
	public function nextToken() {
		//$break = FALSE;
		//while (!$break) {
			while (strpos(" \t\f\n", $this->ch) !== FALSE) {
				$this->nextCh();
			}
			//$break = TRUE;
			// TODO: add code to strip out comments
		//}

		// Initialize the lexeme's position
		$this->start = tx_dbal_sql_Position::encode($this->line, $this->column);
		// Read the lexeme
		$this->token = $this->readToken();
	}

	/**
	 * Reads the next lexeme, stores its representation (if not unique)
	 * into $this->chars and returns its class.
	 *
	 * @return integer
	 */
	private function readToken() {
		$this->chars = '';
		$this->buffer = '';
		switch ($this->ch) {
			case -1:
				return self::EOF;
			case '(':
				return $this->nextCh(self::T_LPAREN);
			case ')':
				return $this->nextCh(self::T_RPAREN);
			case ',':
				return $this->nextCh(self::T_COMMA);
			case ';':
				return $this->nextCh(self::T_SEMICOLON);
			case '?':
				return $this->nextCh(self::T_QUESTION);
			case '+':
				return $this->nextCh(self::T_PLUS);
			case '-':
				return $this->nextCh(self::T_MINUS);
			case '/':
				return $this->nextCh(self::T_DIVIDE);
			case '*':
				return $this->nextCh(self::T_STAR);
			case '%':
				return $this->nextCh(self::T_MODULO);
			case '^':
				return $this->nextCh(self::T_POW);
			case '=':
				return $this->nextCh(self::T_EQUAL);
			case '>':
				$this->nextCh();
				if ($this->ch === '=') {
					return $this->nextCh(self::T_GREATEREQUAL);
				} else {
					return self::T_GREATER;
				}
			case '<':
				$this->nextCh();
				if ($this->ch === '=') {
					return $this->nextCh(self::T_LESSEQUAL);
				} elseif ($this->ch === '>') {
					return $this->nextCh(self::T_BOX);
				} else {
					return self::T_LESS;
				}
			case '!':
				$this->nextCh();
				if ($this->ch === '=') {
					return $this->nextCh(self::T_NOTEQUAL);
				} else {
					return self::T_LOGICNOT;
				}
			case '&':
				$this->nextCh();
				if ($this->ch === '&') {
					return $this->nextCh(self::T_LOGICAND);
				} else {
					return self::T_BITAND;
				}
			case '|':
				$this->nextCh();
				if ($this->ch === '|') {
					return $this->nextCh(self::T_LOGICOR);
				} else {
					return self::T_BITOR;
				}
			case '"':
			case "'":
				$quoteChar = $this->ch;
				$this->buffer .= $this->ch;
				$this->nextCh();
				while ($this->ch !== $quoteChar) {
					if ($this->ch !== "\n") {
						$this->buffer .= $this->ch;
						if ($this->ch === '\\') {
							$this->nextCh();
							if ($this->ch === $quoteChar || $this->ch === '\\') {
								$this->buffer .= $this->ch;
							} else {
								$this->global->error($this->start, 'Invalid escape character');
								return self::BAD;
							}
						}
						$this->nextCh();

					} else {
						$this->global->error($this->start, 'String not ended');
						return self::BAD;
					}
				}
				$this->buffer .= $this->ch;
				$this->nextCh();
				$this->chars = $this->buffer;
				return self::T_STRING;
		}

		if ($this->ch >= '0' && $this->ch <= '9') {
			$this->buffer .= $this->ch;
			$this->nextCh();
			while ($this->ch >= '0' && $this->ch <= '9') {
				$this->buffer .= $this->ch;
				$this->nextCh();
			}
			$this->chars = $this->buffer;
			$this->nextCh();
			return self::T_NUMBER;
		}

		if (($this->ch >= 'a' && $this->ch <= 'z') || ($this->ch >= 'A' && $this->ch <= 'Z')) {
			$this->buffer .= $this->ch;
			$this->nextCh();
			while (($this->ch >= 'a' && $this->ch <= 'z')
					|| ($this->ch >= 'A' && $this->ch <= 'Z')
					|| ($this->ch >= '0' && $this->ch <= '9')
					|| $this->ch === '_' || $this->ch === '.') {
				$this->buffer .= $this->ch;
				$this->nextCh();
			}
			$this->chars = $this->buffer;
			$rep = $this->getReservedWord($this->chars);
			if ($rep != 0) {
				$this->chars = '';
				return $rep;
			} else {
				return self::T_IDENT;
			}
		} else {
			$this->global->error($this->start, 'Invalid character');
			$this->nextCh();
			return self::BAD;
		}
	}

	/**
	 * Returns the current lexeme's representation.
	 *
	 * @return string
	 */
	public function representation() {
		$output = $this->tokenClass($this->token);
		if ($this->token == self::T_NUMBER || $this->token == self::T_IDENT || $this->token == self::T_STRING) {
			$output .= '(' . $this->chars . ')';
		}
		return $output;
	}

	/**
	 * Returns the reserved word lexeme associated to a given string
	 * representation, if it exists.
	 *
	 * @param string $str
	 * @return integer
	 */
	protected function getReservedWord($str) {
		switch (strtoupper($str)) {
			// Functions
			case 'CONCAT':
				return self::T_CONCAT;
			case 'FIND_IN_SET':
				return self::T_FINDINSET;

			// Reserved words
			case 'ACCESSIBLE':
				return self::T_ACCESSIBLE;
			case 'ADD':
				return self::T_ADD;
			case 'ALL':
				return self::T_ALL;
			case 'ALTER':
				return self::T_ALTER;
			case 'ANALYZE':
				return self::T_ANALYZE;
			case 'AND':
				return self::T_AND;
			case 'AS':
				return self::T_AS;
			case 'ASC':
				return self::T_ASC;
			case 'ASENSITIVE':
				return self::T_ASENSITIVE;
			case 'BEFORE':
				return self::T_BEFORE;
			case 'BETWEEN':
				return self::T_BETWEEN;
			case 'BIGINT':
				return self::T_BIGINT;
			case 'BINARY':
				return self::T_BINARY;
			case 'BLOB':
				return self::T_BLOB;
			case 'BOTH':
				return self::T_BOTH;
			case 'BY':
				return self::T_BY;
			case 'CALL':
				return self::T_CALL;
			case 'CASCADE':
				return self::T_CASCADE;
			case 'CASE':
				return self::T_CASE;
			case 'CHANGE':
				return self::T_CHANGE;
			case 'CHAR':
				return self::T_CHAR;
			case 'CHARACTER':
				return self::T_CHARACTER;
			case 'CHECK':
				return self::T_CHECK;
			case 'COLLATE':
				return self::T_COLLATE;
			case 'COLUMN':
				return self::T_COLUMN;
			case 'CONDITION':
				return self::T_CONDITION;
			case 'CONSTRAINT':
				return self::T_CONSTRAINT;
			case 'CONTINUE':
				return self::T_CONTINUE;
			case 'CONVERT':
				return self::T_CONVERT;
			case 'CREATE':
				return self::T_CREATE;
			case 'CROSS':
				return self::T_CROSS;
			case 'CURRENT_DATE':
				return self::T_CURRENT_DATE;
			case 'CURRENT_TIME':
				return self::T_CURRENT_TIME;
			case 'CURRENT_TIMESTAMP':
				return self::T_CURRENT_TIMESTAMP;
			case 'CURRENT_USER':
				return self::T_CURRENT_USER;
			case 'CURSOR':
				return self::T_CURSOR;
			case 'DATABASE':
				return self::T_DATABASE;
			case 'DATABASES':
				return self::T_DATABASES;
			case 'DAY_HOUR':
				return self::T_DAY_HOUR;
			case 'DAY_MICROSECOND':
				return self::T_DAY_MICROSECOND;
			case 'DAY_MINUTE':
				return self::T_DAY_MINUTE;
			case 'DAY_SECOND':
				return self::T_DAY_SECOND;
			case 'DEC':
				return self::T_DEC;
			case 'DECIMAL':
				return self::T_DECIMAL;
			case 'DECLARE':
				return self::T_DECLARE;
			case 'DEFAULT':
				return self::T_DEFAULT;
			case 'DELAYED':
				return self::T_DELAYED;
			case 'DELETE':
				return self::T_DELETE;
			case 'DESC':
				return self::T_DESC;
			case 'DESCRIBE':
				return self::T_DESCRIBE;
			case 'DETERMINISTIC':
				return self::T_DETERMINISTIC;
			case 'DISTINCT':
				return self::T_DISTINCT;
			case 'DISTINCTROW':
				return self::T_DISTINCTROW;
			case 'DIV':
				return self::T_DIV;
			case 'DOUBLE':
				return self::T_DOUBLE;
			case 'DROP':
				return self::T_DROP;
			case 'DUAL':
				return self::T_DUAL;
			case 'EACH':
				return self::T_EACH;
			case 'ELSE':
				return self::T_ELSE;
			case 'ELSEIF':
				return self::T_ELSEIF;
			case 'ENCLOSED':
				return self::T_ENCLOSED;
			case 'ESCAPED':
				return self::T_ESCAPED;
			case 'EXISTS':
				return self::T_EXISTS;
			case 'EXIT':
				return self::T_EXIT;
			case 'EXPLAIN':
				return self::T_EXPLAIN;
			case 'FALSE':
				return self::T_FALSE;
			case 'FETCH':
				return self::T_FETCH;
			case 'FLOAT':
				return self::T_FLOAT;
			case 'FLOAT4':
				return self::T_FLOAT4;
			case 'FLOAT8':
				return self::T_FLOAT8;
			case 'FOR':
				return self::T_FOR;
			case 'FORCE':
				return self::T_FORCE;
			case 'FOREIGN':
				return self::T_FOREIGN;
			case 'FROM':
				return self::T_FROM;
			case 'FULLTEXT':
				return self::T_FULLTEXT;
			case 'GENERAL':
				return self::T_GENERAL;
			case 'GRANT':
				return self::T_GRANT;
			case 'GROUP':
				return self::T_GROUP;
			case 'HAVING':
				return self::T_HAVING;
			case 'HIGH_PRIORITY':
				return self::T_HIGH_PRIORITY;
			case 'HOUR_MICROSECOND':
				return self::T_HOUR_MICROSECOND;
			case 'HOUR_MINUTE':
				return self::T_HOUR_MINUTE;
			case 'HOUR_SECOND':
				return self::T_HOUR_SECOND;
			case 'IF':
				return self::T_IF;
			case 'IGNORE':
				return self::T_IGNORE;
			case 'IGNORE_SERVER_IDS':
				return self::T_IGNORE_SERVER_IDS;
			case 'IN':
				return self::T_IN;
			case 'INDEX':
				return self::T_INDEX;
			case 'INFILE':
				return self::T_INFILE;
			case 'INNER':
				return self::T_INNER;
			case 'INOUT':
				return self::T_INOUT;
			case 'INSENSITIVE':
				return self::T_INSENSITIVE;
			case 'INSERT':
				return self::T_INSERT;
			case 'INT':
				return self::T_INT;
			case 'INT1':
				return self::T_INT1;
			case 'INT2':
				return self::T_INT2;
			case 'INT3':
				return self::T_INT3;
			case 'INT4':
				return self::T_INT4;
			case 'INT8':
				return self::T_INT8;
			case 'INTEGER':
				return self::T_INTEGER;
			case 'INTERVAL':
				return self::T_INTERVAL;
			case 'INTO':
				return self::T_INTO;
			case 'IS':
				return self::T_IS;
			case 'ITERATE':
				return self::T_ITERATE;
			case 'JOIN':
				return self::T_JOIN;
			case 'KEY':
				return self::T_KEY;
			case 'KEYS':
				return self::T_KEYS;
			case 'KILL':
				return self::T_KILL;
			case 'LEADING':
				return self::T_LEADING;
			case 'LEAVE':
				return self::T_LEAVE;
			case 'LEFT':
				return self::T_LEFT;
			case 'LIKE':
				return self::T_LIKE;
			case 'LIMIT':
				return self::T_LIMIT;
			case 'LINEAR':
				return self::T_LINEAR;
			case 'LINES':
				return self::T_LINES;
			case 'LOAD':
				return self::T_LOAD;
			case 'LOCALTIME':
				return self::T_LOCALTIME;
			case 'LOCALTIMESTAMP':
				return self::T_LOCALTIMESTAMP;
			case 'LOCK':
				return self::T_LOCK;
			case 'LONG':
				return self::T_LONG;
			case 'LONGBLOB':
				return self::T_LONGBLOB;
			case 'LONGTEXT':
				return self::T_LONGTEXT;
			case 'LOOP':
				return self::T_LOOP;
			case 'LOW_PRIORITY':
				return self::T_LOW_PRIORITY;
			case 'MASTER_HEARTBEAT_PERIOD':
				return self::T_MASTER_HEARTBEAT_PERIOD;
			case 'MASTER_SSL_VERIFY_SERVER_CERT':
				return self::T_MASTER_SSL_VERIFY_SERVER_CERT;
			case 'MATCH':
				return self::T_MATCH;
			case 'MAXVALUE':
				return self::T_MAXVALUE;
			case 'MEDIUMBLOB':
				return self::T_MEDIUMBLOB;
			case 'MEDIUMINT':
				return self::T_MEDIUMINT;
			case 'MEDIUMTEXT':
				return self::T_MEDIUMTEXT;
			case 'MIDDLEINT':
				return self::T_MIDDLEINT;
			case 'MINUTE_MICROSECOND':
				return self::T_MINUTE_MICROSECOND;
			case 'MINUTE_SECOND':
				return self::T_MINUTE_SECOND;
			case 'MOD':
				return self::T_MOD;
			case 'MODIFIES':
				return self::T_MODIFIES;
			case 'NATURAL':
				return self::T_NATURAL;
			case 'NOT':
				return self::T_NOT;
			case 'NO_WRITE_TO_BINLOG':
				return self::T_NO_WRITE_TO_BINLOG;
			case 'NULL':
				return self::T_NULL;
			case 'NUMERIC':
				return self::T_NUMERIC;
			case 'ON':
				return self::T_ON;
			case 'OPTIMIZE':
				return self::T_OPTIMIZE;
			case 'OPTION':
				return self::T_OPTION;
			case 'OPTIONALLY':
				return self::T_OPTIONALLY;
			case 'OR':
				return self::T_OR;
			case 'ORDER':
				return self::T_ORDER;
			case 'OUT':
				return self::T_OUT;
			case 'OUTER':
				return self::T_OUTER;
			case 'OUTFILE':
				return self::T_OUTFILE;
			case 'PRECISION':
				return self::T_PRECISION;
			case 'PRIMARY':
				return self::T_PRIMARY;
			case 'PROCEDURE':
				return self::T_PROCEDURE;
			case 'PURGE':
				return self::T_PURGE;
			case 'RANGE':
				return self::T_RANGE;
			case 'READ':
				return self::T_READ;
			case 'READS':
				return self::T_READS;
			case 'READ_WRITE':
				return self::T_READ_WRITE;
			case 'REAL':
				return self::T_REAL;
			case 'REFERENCES':
				return self::T_REFERENCES;
			case 'REGEXP':
				return self::T_REGEXP;
			case 'RELEASE':
				return self::T_RELEASE;
			case 'RENAME':
				return self::T_RENAME;
			case 'REPEAT':
				return self::T_REPEAT;
			case 'REPLACE':
				return self::T_REPLACE;
			case 'REQUIRE':
				return self::T_REQUIRE;
			case 'RESIGNAL':
				return self::T_RESIGNAL;
			case 'RESTRICT':
				return self::T_RESTRICT;
			case 'RETURN':
				return self::T_RETURN;
			case 'REVOKE':
				return self::T_REVOKE;
			case 'RIGHT':
				return self::T_RIGHT;
			case 'RLIKE':
				return self::T_RLIKE;
			case 'SCHEMA':
				return self::T_SCHEMA;
			case 'SCHEMAS':
				return self::T_SCHEMAS;
			case 'SECOND_MICROSECOND':
				return self::T_SECOND_MICROSECOND;
			case 'SELECT':
				return self::T_SELECT;
			case 'SENSITIVE':
				return self::T_SENSITIVE;
			case 'SEPARATOR':
				return self::T_SEPARATOR;
			case 'SET':
				return self::T_SET;
			case 'SHOW':
				return self::T_SHOW;
			case 'SIGNAL':
				return self::T_SIGNAL;
			case 'SLOW':
				return self::T_SLOW;
			case 'SMALLINT':
				return self::T_SMALLINT;
			case 'SPATIAL':
				return self::T_SPATIAL;
			case 'SPECIFIC':
				return self::T_SPECIFIC;
			case 'SQL':
				return self::T_SQL;
			case 'SQLEXCEPTION':
				return self::T_SQLEXCEPTION;
			case 'SQLSTATE':
				return self::T_SQLSTATE;
			case 'SQLWARNING':
				return self::T_SQLWARNING;
			case 'SQL_BIG_RESULT':
				return self::T_SQL_BIG_RESULT;
			case 'SQL_CALC_FOUND_ROWS':
				return self::T_SQL_CALC_FOUND_ROWS;
			case 'SQL_SMALL_RESULT':
				return self::T_SQL_SMALL_RESULT;
			case 'SSL':
				return self::T_SSL;
			case 'STARTING':
				return self::T_STARTING;
			case 'STRAIGHT_JOIN':
				return self::T_STRAIGHT_JOIN;
			case 'TABLE':
				return self::T_TABLE;
			case 'TERMINATED':
				return self::T_TERMINATED;
			case 'THEN':
				return self::T_THEN;
			case 'TINYBLOB':
				return self::T_TINYBLOB;
			case 'TINYINT':
				return self::T_TINYINT;
			case 'TINYTEXT':
				return self::T_TINYTEXT;
			case 'TO':
				return self::T_TO;
			case 'TRAILING':
				return self::T_TRAILING;
			case 'TRIGGER':
				return self::T_TRIGGER;
			case 'TRUE':
				return self::T_TRUE;
			case 'UNDO':
				return self::T_UNDO;
			case 'UNION':
				return self::T_UNION;
			case 'UNIQUE':
				return self::T_UNIQUE;
			case 'UNLOCK':
				return self::T_UNLOCK;
			case 'UNSIGNED':
				return self::T_UNSIGNED;
			case 'UPDATE':
				return self::T_UPDATE;
			case 'USAGE':
				return self::T_USAGE;
			case 'USE':
				return self::T_USE;
			case 'USING':
				return self::T_USING;
			case 'UTC_DATE':
				return self::T_UTC_DATE;
			case 'UTC_TIME':
				return self::T_UTC_TIME;
			case 'UTC_TIMESTAMP':
				return self::T_UTC_TIMESTAMP;
			case 'VALUES':
				return self::T_VALUES;
			case 'VARBINARY':
				return self::T_VARBINARY;
			case 'VARCHAR':
				return self::T_VARCHAR;
			case 'VARCHARACTER':
				return self::T_VARCHARACTER;
			case 'VARYING':
				return self::T_VARYING;
			case 'WHEN':
				return self::T_WHEN;
			case 'WHERE':
				return self::T_WHERE;
			case 'WHILE':
				return self::T_WHILE;
			case 'WITH':
				return self::T_WITH;
			case 'WRITE':
				return self::T_WRITE;
			case 'XOR':
				return self::T_XOR;
			case 'YEAR_MONTH':
				return self::T_YEAR_MONTH;
			case 'ZEROFILL':
				return self::T_ZEROFILL;

			default:
				return 0;
		}
	}

	/**
	 * Returns the representation of a given lexeme.
	 *
	 * @param integer $token
	 * @return string
	 * @throws tx_dbal_sql_error_UnknownToken
	 * @static
	 */
	public static function tokenClass($token) {
		switch ($token) {
			case self::EOF                             : return '<eof>';
			case self::BAD                             : return '<bad>';

			case self::T_IDENT                         : return 'ident';
			case self::T_NUMBER                        : return 'number';
			case self::T_STRING                        : return 'string';

			case self::T_LPAREN                        : return '(';
			case self::T_RPAREN                        : return ')';
			case self::T_COMMA                         : return ',';
			case self::T_SEMICOLON                     : return ';';
			case self::T_QUESTION                      : return '?';

			// Operators
			case self::T_PLUS                          : return '+';
			case self::T_MINUS                         : return '-';
			case self::T_DIVIDE                        : return '/';
			case self::T_STAR                          : return '*';
			case self::T_MODULO                        : return '%';
			case self::T_POW                           : return '^';
			case self::T_EQUAL                         : return '=';
			case self::T_GREATEREQUAL                  : return '>=';
			case self::T_GREATER                       : return '>';
			case self::T_LESSEQUAL                     : return '<=';
			case self::T_LESS                          : return '<';
			case self::T_BOX                           : return '<>';
			case self::T_NOTEQUAL                      : return '!=';
			case self::T_LOGICNOT                      : return '!';
			case self::T_LOGICAND                      : return '&&';
			case self::T_LOGICOR                       : return '||';
			case self::T_BITAND                        : return '&';
			case self::T_BITOR                         : return '|';

			// Functions
			case self::T_CONCAT                        : return 'CONCAT';
			case self::T_FINDINSET                     : return 'FIND_IN_SET';

			// Reserved words
			case self::T_ACCESSIBLE                    : return 'ACCESSIBLE';
			case self::T_ADD                           : return 'ADD';
			case self::T_ALL                           : return 'ALL';
			case self::T_ALTER                         : return 'ALTER';
			case self::T_ANALYZE                       : return 'ANALYZE';
			case self::T_AND                           : return 'AND';
			case self::T_AS                            : return 'AS';
			case self::T_ASC                           : return 'ASC';
			case self::T_ASENSITIVE                    : return 'ASENSITIVE';
			case self::T_BEFORE                        : return 'BEFORE';
			case self::T_BETWEEN                       : return 'BETWEEN';
			case self::T_BIGINT                        : return 'BIGINT';
			case self::T_BINARY                        : return 'BINARY';
			case self::T_BLOB                          : return 'BLOB';
			case self::T_BOTH                          : return 'BOTH';
			case self::T_BY                            : return 'BY';
			case self::T_CALL                          : return 'CALL';
			case self::T_CASCADE                       : return 'CASCADE';
			case self::T_CASE                          : return 'CASE';
			case self::T_CHANGE                        : return 'CHANGE';
			case self::T_CHAR                          : return 'CHAR';
			case self::T_CHARACTER                     : return 'CHARACTER';
			case self::T_CHECK                         : return 'CHECK';
			case self::T_COLLATE                       : return 'COLLATE';
			case self::T_COLUMN                        : return 'COLUMN';
			case self::T_CONDITION                     : return 'CONDITION';
			case self::T_CONSTRAINT                    : return 'CONSTRAINT';
			case self::T_CONTINUE                      : return 'CONTINUE';
			case self::T_CONVERT                       : return 'CONVERT';
			case self::T_CREATE                        : return 'CREATE';
			case self::T_CROSS                         : return 'CROSS';
			case self::T_CURRENT_DATE                  : return 'CURRENT_DATE';
			case self::T_CURRENT_TIME                  : return 'CURRENT_TIME';
			case self::T_CURRENT_TIMESTAMP             : return 'CURRENT_TIMESTAMP';
			case self::T_CURRENT_USER                  : return 'CURRENT_USER';
			case self::T_CURSOR                        : return 'CURSOR';
			case self::T_DATABASE                      : return 'DATABASE';
			case self::T_DATABASES                     : return 'DATABASES';
			case self::T_DAY_HOUR                      : return 'DAY_HOUR';
			case self::T_DAY_MICROSECOND               : return 'DAY_MICROSECOND';
			case self::T_DAY_MINUTE                    : return 'DAY_MINUTE';
			case self::T_DAY_SECOND                    : return 'DAY_SECOND';
			case self::T_DEC                           : return 'DEC';
			case self::T_DECIMAL                       : return 'DECIMAL';
			case self::T_DECLARE                       : return 'DECLARE';
			case self::T_DEFAULT                       : return 'DEFAULT';
			case self::T_DELAYED                       : return 'DELAYED';
			case self::T_DELETE                        : return 'DELETE';
			case self::T_DESC                          : return 'DESC';
			case self::T_DESCRIBE                      : return 'DESCRIBE';
			case self::T_DETERMINISTIC                 : return 'DETERMINISTIC';
			case self::T_DISTINCT                      : return 'DISTINCT';
			case self::T_DISTINCTROW                   : return 'DISTINCTROW';
			case self::T_DIV                           : return 'DIV';
			case self::T_DOUBLE                        : return 'DOUBLE';
			case self::T_DROP                          : return 'DROP';
			case self::T_DUAL                          : return 'DUAL';
			case self::T_EACH                          : return 'EACH';
			case self::T_ELSE                          : return 'ELSE';
			case self::T_ELSEIF                        : return 'ELSEIF';
			case self::T_ENCLOSED                      : return 'ENCLOSED';
			case self::T_ESCAPED                       : return 'ESCAPED';
			case self::T_EXISTS                        : return 'EXISTS';
			case self::T_EXIT                          : return 'EXIT';
			case self::T_EXPLAIN                       : return 'EXPLAIN';
			case self::T_FALSE                         : return 'FALSE';
			case self::T_FETCH                         : return 'FETCH';
			case self::T_FLOAT                         : return 'FLOAT';
			case self::T_FLOAT4                        : return 'FLOAT4';
			case self::T_FLOAT8                        : return 'FLOAT8';
			case self::T_FOR                           : return 'FOR';
			case self::T_FORCE                         : return 'FORCE';
			case self::T_FOREIGN                       : return 'FOREIGN';
			case self::T_FROM                          : return 'FROM';
			case self::T_FULLTEXT                      : return 'FULLTEXT';
			case self::T_GENERAL                       : return 'GENERAL';
			case self::T_GRANT                         : return 'GRANT';
			case self::T_GROUP                         : return 'GROUP';
			case self::T_HAVING                        : return 'HAVING';
			case self::T_HIGH_PRIORITY                 : return 'HIGH_PRIORITY';
			case self::T_HOUR_MICROSECOND              : return 'HOUR_MICROSECOND';
			case self::T_HOUR_MINUTE                   : return 'HOUR_MINUTE';
			case self::T_HOUR_SECOND                   : return 'HOUR_SECOND';
			case self::T_IF                            : return 'IF';
			case self::T_IGNORE                        : return 'IGNORE';
			case self::T_IGNORE_SERVER_IDS             : return 'IGNORE_SERVER_IDS';
			case self::T_IN                            : return 'IN';
			case self::T_INDEX                         : return 'INDEX';
			case self::T_INFILE                        : return 'INFILE';
			case self::T_INNER                         : return 'INNER';
			case self::T_INOUT                         : return 'INOUT';
			case self::T_INSENSITIVE                   : return 'INSENSITIVE';
			case self::T_INSERT                        : return 'INSERT';
			case self::T_INT                           : return 'INT';
			case self::T_INT1                          : return 'INT1';
			case self::T_INT2                          : return 'INT2';
			case self::T_INT3                          : return 'INT3';
			case self::T_INT4                          : return 'INT4';
			case self::T_INT8                          : return 'INT8';
			case self::T_INTEGER                       : return 'INTEGER';
			case self::T_INTERVAL                      : return 'INTERVAL';
			case self::T_INTO                          : return 'INTO';
			case self::T_IS                            : return 'IS';
			case self::T_ITERATE                       : return 'ITERATE';
			case self::T_JOIN                          : return 'JOIN';
			case self::T_KEY                           : return 'KEY';
			case self::T_KEYS                          : return 'KEYS';
			case self::T_KILL                          : return 'KILL';
			case self::T_LEADING                       : return 'LEADING';
			case self::T_LEAVE                         : return 'LEAVE';
			case self::T_LEFT                          : return 'LEFT';
			case self::T_LIKE                          : return 'LIKE';
			case self::T_LIMIT                         : return 'LIMIT';
			case self::T_LINEAR                        : return 'LINEAR';
			case self::T_LINES                         : return 'LINES';
			case self::T_LOAD                          : return 'LOAD';
			case self::T_LOCALTIME                     : return 'LOCALTIME';
			case self::T_LOCALTIMESTAMP                : return 'LOCALTIMESTAMP';
			case self::T_LOCK                          : return 'LOCK';
			case self::T_LONG                          : return 'LONG';
			case self::T_LONGBLOB                      : return 'LONGBLOB';
			case self::T_LONGTEXT                      : return 'LONGTEXT';
			case self::T_LOOP                          : return 'LOOP';
			case self::T_LOW_PRIORITY                  : return 'LOW_PRIORITY';
			case self::T_MASTER_HEARTBEAT_PERIOD       : return 'MASTER_HEARTBEAT_PERIOD';
			case self::T_MASTER_SSL_VERIFY_SERVER_CERT : return 'MASTER_SSL_VERIFY_SERVER_CERT';
			case self::T_MATCH                         : return 'MATCH';
			case self::T_MAXVALUE                      : return 'MAXVALUE';
			case self::T_MEDIUMBLOB                    : return 'MEDIUMBLOB';
			case self::T_MEDIUMINT                     : return 'MEDIUMINT';
			case self::T_MEDIUMTEXT                    : return 'MEDIUMTEXT';
			case self::T_MIDDLEINT                     : return 'MIDDLEINT';
			case self::T_MINUTE_MICROSECOND            : return 'MINUTE_MICROSECOND';
			case self::T_MINUTE_SECOND                 : return 'MINUTE_SECOND';
			case self::T_MOD                           : return 'MOD';
			case self::T_MODIFIES                      : return 'MODIFIES';
			case self::T_NATURAL                       : return 'NATURAL';
			case self::T_NOT                           : return 'NOT';
			case self::T_NO_WRITE_TO_BINLOG            : return 'NO_WRITE_TO_BINLOG';
			case self::T_NULL                          : return 'NULL';
			case self::T_NUMERIC                       : return 'NUMERIC';
			case self::T_ON                            : return 'ON';
			case self::T_OPTIMIZE                      : return 'OPTIMIZE';
			case self::T_OPTION                        : return 'OPTION';
			case self::T_OPTIONALLY                    : return 'OPTIONALLY';
			case self::T_OR                            : return 'OR';
			case self::T_ORDER                         : return 'ORDER';
			case self::T_OUT                           : return 'OUT';
			case self::T_OUTER                         : return 'OUTER';
			case self::T_OUTFILE                       : return 'OUTFILE';
			case self::T_PRECISION                     : return 'PRECISION';
			case self::T_PRIMARY                       : return 'PRIMARY';
			case self::T_PROCEDURE                     : return 'PROCEDURE';
			case self::T_PURGE                         : return 'PURGE';
			case self::T_RANGE                         : return 'RANGE';
			case self::T_READ                          : return 'READ';
			case self::T_READS                         : return 'READS';
			case self::T_READ_WRITE                    : return 'READ_WRITE';
			case self::T_REAL                          : return 'REAL';
			case self::T_REFERENCES                    : return 'REFERENCES';
			case self::T_REGEXP                        : return 'REGEXP';
			case self::T_RELEASE                       : return 'RELEASE';
			case self::T_RENAME                        : return 'RENAME';
			case self::T_REPEAT                        : return 'REPEAT';
			case self::T_REPLACE                       : return 'REPLACE';
			case self::T_REQUIRE                       : return 'REQUIRE';
			case self::T_RESIGNAL                      : return 'RESIGNAL';
			case self::T_RESTRICT                      : return 'RESTRICT';
			case self::T_RETURN                        : return 'RETURN';
			case self::T_REVOKE                        : return 'REVOKE';
			case self::T_RIGHT                         : return 'RIGHT';
			case self::T_RLIKE                         : return 'RLIKE';
			case self::T_SCHEMA                        : return 'SCHEMA';
			case self::T_SCHEMAS                       : return 'SCHEMAS';
			case self::T_SECOND_MICROSECOND            : return 'SECOND_MICROSECOND';
			case self::T_SELECT                        : return 'SELECT';
			case self::T_SENSITIVE                     : return 'SENSITIVE';
			case self::T_SEPARATOR                     : return 'SEPARATOR';
			case self::T_SET                           : return 'SET';
			case self::T_SHOW                          : return 'SHOW';
			case self::T_SIGNAL                        : return 'SIGNAL';
			case self::T_SLOW                          : return 'SLOW';
			case self::T_SMALLINT                      : return 'SMALLINT';
			case self::T_SPATIAL                       : return 'SPATIAL';
			case self::T_SPECIFIC                      : return 'SPECIFIC';
			case self::T_SQL                           : return 'SQL';
			case self::T_SQLEXCEPTION                  : return 'SQLEXCEPTION';
			case self::T_SQLSTATE                      : return 'SQLSTATE';
			case self::T_SQLWARNING                    : return 'SQLWARNING';
			case self::T_SQL_BIG_RESULT                : return 'SQL_BIG_RESULT';
			case self::T_SQL_CALC_FOUND_ROWS           : return 'SQL_CALC_FOUND_ROWS';
			case self::T_SQL_SMALL_RESULT              : return 'SQL_SMALL_RESULT';
			case self::T_SSL                           : return 'SSL';
			case self::T_STARTING                      : return 'STARTING';
			case self::T_STRAIGHT_JOIN                 : return 'STRAIGHT_JOIN';
			case self::T_TABLE                         : return 'TABLE';
			case self::T_TERMINATED                    : return 'TERMINATED';
			case self::T_THEN                          : return 'THEN';
			case self::T_TINYBLOB                      : return 'TINYBLOB';
			case self::T_TINYINT                       : return 'TINYINT';
			case self::T_TINYTEXT                      : return 'TINYTEXT';
			case self::T_TO                            : return 'TO';
			case self::T_TRAILING                      : return 'TRAILING';
			case self::T_TRIGGER                       : return 'TRIGGER';
			case self::T_TRUE                          : return 'TRUE';
			case self::T_UNDO                          : return 'UNDO';
			case self::T_UNION                         : return 'UNION';
			case self::T_UNIQUE                        : return 'UNIQUE';
			case self::T_UNLOCK                        : return 'UNLOCK';
			case self::T_UNSIGNED                      : return 'UNSIGNED';
			case self::T_UPDATE                        : return 'UPDATE';
			case self::T_USAGE                         : return 'USAGE';
			case self::T_USE                           : return 'USE';
			case self::T_USING                         : return 'USING';
			case self::T_UTC_DATE                      : return 'UTC_DATE';
			case self::T_UTC_TIME                      : return 'UTC_TIME';
			case self::T_UTC_TIMESTAMP                 : return 'UTC_TIMESTAMP';
			case self::T_VALUES                        : return 'VALUES';
			case self::T_VARBINARY                     : return 'VARBINARY';
			case self::T_VARCHAR                       : return 'VARCHAR';
			case self::T_VARCHARACTER                  : return 'VARCHARACTER';
			case self::T_VARYING                       : return 'VARYING';
			case self::T_WHEN                          : return 'WHEN';
			case self::T_WHERE                         : return 'WHERE';
			case self::T_WHILE                         : return 'WHILE';
			case self::T_WITH                          : return 'WITH';
			case self::T_WRITE                         : return 'WRITE';
			case self::T_XOR                           : return 'XOR';
			case self::T_YEAR_MONTH                    : return 'YEAR_MONTH';
			case self::T_ZEROFILL                      : return 'ZEROFILL';

			default:
				throw t3lib_div::makeInstance('tx_dbal_sql_error_UnknownToken', $token);
		}
	}

	/**
	 * Puts next character in $this->ch and updates the position.
	 *
	 * @param integer $token
	 * @return integer
	 */
	private function nextCh($token = 0) {
		switch ($this->ch) {
			case self::EOF:
				return;
			case "\n":
				$this->column = 1;
				$this->line++;
				break;
			default:
				$this->column++;
		}

		$this->ch = $this->in->read(1);
		$this->oldCh = (($this->oldCh === "\r") && ($this->ch === "\n")) ? $this->in->read(1) : $this->ch;
		$this->ch = ($this->oldCh === "\r") ? "\n" : $this->oldCh;

		return $token;
	}
}

?>