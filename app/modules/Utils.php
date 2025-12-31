<?php

/**
 * A module to implement various utilities
 */

class Utils {
    const COUNT_MILLION = 1000000;
    const COUNT_HUNDREDTHOUSAND = 100000;
    const COUNT_TENTHOUSAND = 10000;
    const COUNT_THOUSAND = 1000;

    /**
     * @return int
     */
    public static function getVisitorCount()
    {
        try {
			return Cache::remember('visitor_counter', (int)env('APP_CACHE_DURATION', 125), function() {
				return Utils::countAsString(Counter::getCount());
			});
		} catch (\Exception $e) {
			return 0;
		}
    }

    /**
     * @param $token
     * @return int
     */
    public static function getViewerCount($token)
    {
        try {
			return Cache::remember('viewer_counter_' . $token, (int)env('APP_CACHE_DURATION', 125), function() {
				return Utils::countAsString(Counter::getForRequestUri($_SERVER['REQUEST_URI']));
			});
		} catch (\Exception $e) {
			return -1;
		}
    }

    /**
     * @return array
     */
    public static function getPopularBlogPosts($limit = 5)
    {
        try {
            $posts = Blog::fetch()->asArray();
            foreach ($posts as $key => &$post) {
                $vc = Counter::getForRequestUri('/blog/' . $post['slug']);
                $posts[$key]['views'] = intval($vc);
            }

            usort($posts, function($item1, $item2) {
                return $item2['views'] <=> $item1['views'];
            });

            if ($limit) {
                $posts = array_slice($posts, 0, $limit);
            }

            return $posts;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return array
     */
    public static function getBackgroundImageList()
    {
        try {
            return json_decode(Cache::remember('background_images', (int)env('APP_CACHE_DURATION', 125), function() {
                $list = [];

				$files = scandir(public_path() . '/img/backgrounds');
                foreach ($files as $file) {
                    if (substr($file, 0, 1) === '.') {
                        continue;
                    }

                    if (static::isValidImage(public_path() . '/img/backgrounds/' . $file)) {
                        $list[] = $file;
                    }
                }

                return json_encode($list);
			}));
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * @param $count
     * @return string
     * @throws Exception
     */
    public static function countAsString($count)
    {
        try {
            if ($count >= self::COUNT_MILLION) {
                return strval(round($count / self::COUNT_MILLION, 1)) . 'M';
            } else if (($count < self::COUNT_MILLION) && ($count >= self::COUNT_HUNDREDTHOUSAND)) {
                return strval(round($count / self::COUNT_THOUSAND, 1)) . 'K';
            } else if (($count < self::COUNT_HUNDREDTHOUSAND) && ($count >= self::COUNT_TENTHOUSAND)) {
                return strval(round($count / self::COUNT_THOUSAND, 1)) . 'K';
            } else if (($count < self::COUNT_TENTHOUSAND) && ($count >= self::COUNT_THOUSAND)) {
                return strval(round($count / self::COUNT_THOUSAND, 1)) . 'K';
            } else {
                return strval($count);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $file
     * @return mixed|null
     */
    public static function getImageExt($file)
    {
        $imagetypes = [
            'png' => IMAGETYPE_PNG,
            'jpg' => IMAGETYPE_JPEG,
            'gif' => IMAGETYPE_GIF
        ];

        foreach ($imagetypes as $ext => $type) {
            if (exif_imagetype($file) === $type) {
                return $ext;
            }
        }

        return null;
    }

    /**
     * @param $file
     * @return bool
     */
    public static function isValidImage($file)
    {
        $imagetypes = [
            IMAGETYPE_PNG,
            IMAGETYPE_JPEG,
            IMAGETYPE_GIF
        ];

        foreach ($imagetypes as $type) {
            if (exif_imagetype($file) === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $content
     * @return string
     */
    public static function descriptify($content)
    {
        $content = str_replace("\r\n", '  ', $content);
        $content = strip_tags($content);
        $content = substr($content, 0, 63) . '...';

        return $content;
    }

    /**
     * @param string $expression
     * @param string $delimiter
     * @return string
     */
    public static function pascalify($expression, $delimiter = ' ')
    {
        $tokens = explode($delimiter, $expression);

        foreach ($tokens as $key => &$token) {
            $tokens[$key] = ucfirst($token);
        }

        return implode('', $tokens);
    }

    /**
     * @param string $expression
     * @param string $delimiter
     * @return string
     */
    public static function ruleify($expression, $delimiter = ' ')
    {
        $tokens = explode($delimiter, $expression);

        foreach ($tokens as $key => &$token) {
            $tokens[$key] = strtolower($token);
        }

        return implode('-', $tokens);
    }

    /**
     * @return string
     */
    public static function getDefaultMetaImage()
    {
        if (file_exists(public_path() . '/img/preview.png')) {
            return asset('img/preview.png');
        }

        return asset('img/logo.png');
    }

    /**
     * @param $value
     * @return string
     */
    public static function writeVarInt($value)
    {
        $out = '';

        while (true) {
            if (($value & ~0x7F) === 0) {
                $out .= chr($value);

                return $out;
            }

            $out .= chr(($value & 0x7F) | 0x80);
            $value >>= 7;
        }
    }

    /**
     * @param $socket
     * @return int
     * @throws \Exception
     */
    public static function readVarInt($socket)
    {
        $numRead = 0;
        $result = 0;

        do {
            $byte = ord(fread($socket, 1));
            $result |= ($byte & 0x7F) << (7 * $numRead);

            $numRead++;

            if ($numRead > 5) {
                throw new \Exception('VarInt too big');
            }
        } while (($byte & 0x80) === 0x80);
        
        return $result;
    }
}
